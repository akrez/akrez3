<?php

namespace app\models;

use app\components\Image;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "gallery".
 *
 * @property string $name
 * @property int $updated_at
 * @property string $status
 * @property int $width
 * @property int $height
 * @property string $type
 * @property int $product_id
 * @property int $user_id
 * @property string $blog_name
 *
 * @property Blog[] $blogs
 * @property User $user
 * @property Blog $blogName
 * @property Product $product
 */
class Gallery extends ActiveRecord
{

    public $image;

    const TYPE_PRODUCT = 'product';
    const TYPE_LOGO = 'logo';
    const TYPE_AVATAR = 'avatar';

    public static function typeList()
    {
        return [
            self::TYPE_PRODUCT => Yii::t('app', 'product'),
            self::TYPE_LOGO => Yii::t('app', 'logo'),
            self::TYPE_AVATAR => Yii::t('app', 'avatar'),
        ];
    }

    public static function tableName()
    {
        return 'gallery';
    }

    public function rules()
    {
        return [
            [['type', 'name', 'status', 'width', 'height'], 'required'],
            [['updated_at', 'width', 'height', 'product_id', 'user_id'], 'integer'],
            [['name'], 'string', 'max' => 16],
            [['type'], 'string', 'max' => 12],
            [['blog_name'], 'string', 'max' => 31],
            [['name'], 'unique'],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            [['type'], 'in', 'range' => array_keys(self::typeList())],
            [['blog_name'], 'required', 'when' => function ($model) {
                    return $model->type == 'logo';
                }],
            [['product_id'], 'required', 'when' => function ($model) {
                    return $model->type == 'product';
                }],
            [['user_id'], 'required', 'when' => function ($model) {
                    return $model->type == 'avatar';
                }],
        ];
    }

    /*
     * PUBLIC UPLOAD HADLER
     */

    public function upload($type, $srcFile, $params = [])
    {
        $handler = new Image();
        $handler->save($srcFile, $type, null, null, null, false);
        $error = $handler->getError();
        if ($error) {
            return [$error];
        }

        $info = $handler->getInfo();

        $params = $params + [
            'status' => Status::STATUS_ACTIVE,
            'blog_name' => Yii::$app->blog->name(),
            'user_id' => Yii::$app->user->getId(),
            'product_id' => null,
        ];

        $this->type = $type;
        $this->status = $params['status'];
        $this->blog_name = $params['blog_name'];
        $this->user_id = $params['user_id'];
        $this->product_id = $params['product_id'];
        $this->width = $info['desWidth'];
        $this->height = $info['desHeight'];
        $this->name = $info['desName'];
        $this->save();

        return $this->getErrorSummary(true);
    }

    public function delete()
    {
        $path = Image::getOriginalImagePath($this->type, $this->name);
        @unlink($path);
        return parent::delete();
    }

    public static function cache($type, $name, $whqm)
    {
        try {
            $whqm = explode('_', $whqm) + [0, 0, 0, 0];
            $whqm = array_map('intval', $whqm);

            if (!preg_match("/^[A-Za-z0-9.]+$/i", $name)) {
                return null;
            }

            $destination = Image::getCacheImagePath($type, implode('_', $whqm), $name);
            if (file_exists($destination)) {
                return $destination;
            }

            $source = Image::getOriginalImagePath($type, $name);
            if (!file_exists($source)) {
                return null;
            }

            $handler = new Image();
            if ($handler->save($source, $destination, $whqm[0], $whqm[1], $whqm[2], true, $whqm[3])) {
                return $destination;
            }
        } catch (\Exception $e) {
            
        }

        return null;
    }

}
