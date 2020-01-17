<?php

namespace app\models;

/**
 * This is the model class for table "blog".
 *
 * @property string $name
 * @property string $updated_at
 * @property string $created_at
 * @property int $status
 * @property string $title
 * @property string $slug
 * @property string $des
 * @property string $logo
 * @property int $user_id
 *
 * @property User $user
 * @property Category[] $categories
 * @property Field[] $fields
 * @property Gallery[] $galleries
 * @property Package[] $packages
 * @property Product[] $products
 * @property ProductField[] $productFields
 */
class Blog extends ActiveRecord
{

    public $image;

    public static function tableName()
    {
        return 'blog';
    }

    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['name', 'status', 'title', '!user_id'], 'required'],
            [['name'], 'string', 'max' => 31],
            [['title'], 'string', 'max' => 63],
            [['name'], 'match', 'pattern' => '/^[a-z]+$/', 'message' => 'فقط از حروف کوچک انگلیسی بدون فاصله استفاده کنید.'],
            [['slug'], 'string', 'max' => 127],
            [['des'], 'string', 'max' => 1023],
            [['logo'], 'string', 'max' => 16],
            [['name'], 'unique'],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            [['!name'], 'string', 'max' => 31, 'when' => function($model) {
                    return !$model->isNewRecord;
            }],
        ];
    }

    public function info()
    {
        return [
            'created_at' => $this->created_at,
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'des' => $this->des,
            'logo' => $this->logo,
        ];
    }

    public static function findBlogForAdmin($name, $user_id)
    {
        return static::find()->where(['name' => $name, 'user_id' => $user_id, 'status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public static function findBlogsForAdmin($user_id)
    {
        return static::find()->where(['user_id' => $user_id, 'status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->all();
    }

    public static function findBlogForClient($name)
    {
        return static::find()->where(['name' => $name, 'status' => Status::STATUS_ACTIVE])->one();
    }

}
