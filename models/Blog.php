<?php

namespace app\models;

use yii\helpers\Json;

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

    public $des;
    //
    public $address;
    //
    public $email;
    public $phone;
    public $mobile;
    //
    public $instagram;
    public $telegram;
    public $facebook;
    public $twitter;
    //
    public $apiConstantId = '3ar8c9cfb05d8865d0ad9c02enffae40';
    public $image;

    public static function tableName()
    {
        return 'blog';
    }

    public function rules()
    {
        $rules = [
            [['status'], 'integer'],
            [['name', 'status', 'title', '!user_id'], 'required'],
            [['title'], 'string', 'max' => 63],
            [['name'], 'match', 'pattern' => '/^[a-z]+$/', 'message' => 'فقط از حروف کوچک انگلیسی بدون فاصله استفاده کنید.'],
            [['slug'], 'string', 'max' => 127],
            [['logo'], 'string', 'max' => 16],
            [['name'], 'unique'],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            //
            [['email'], 'email'],
            [['facebook', 'instagram'], 'match', 'pattern' => '/^[a-z\d.]{5,}$/i'],
            [['telegram'], 'match', 'pattern' => '/^[a-z\d.]+$/i'],
            [['phone', 'mobile'], 'match', 'pattern' => '/^[0-9+]+$/'],
            [['twitter'], 'match', 'pattern' => '/^[A-Za-z0-9_]{1,15}$/'],
            [['address', 'des'], 'string'],
        ];
        if ($this->isNewRecord) {
            $rules[] = [['name'], 'string', 'max' => 31];
        } else {
            $rules[] = [['!name'], 'string', 'max' => 31];
        }
        return $rules;
    }

    public function afterFind()
    {
        parent::afterFind();

        $arrayParams = (array) Json::decode($this->params) + [
            'address' => null,
            'phone' => null,
            'mobile' => null,
            'email' => null,
            'instagram' => null,
            'telegram' => null,
            'facebook' => null,
            'twitter' => null,
            'des' => null,
        ];
        $this->address = $arrayParams['address'];
        $this->phone = $arrayParams['phone'];
        $this->mobile = $arrayParams['mobile'];
        $this->email = $arrayParams['email'];
        $this->instagram = $arrayParams['instagram'];
        $this->telegram = $arrayParams['telegram'];
        $this->facebook = $arrayParams['facebook'];
        $this->twitter = $arrayParams['twitter'];
        $this->des = $arrayParams['des'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->params = [
            'address' => $this->address,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'instagram' => $this->instagram,
            'telegram' => $this->telegram,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'des' => $this->des,
        ];
        $this->params = Json::encode($this->params);
        return true;
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
            'constant_id' => $this->apiConstantId,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'instagram' => $this->instagram,
            'telegram' => $this->telegram,
            'address' => $this->address,
            'twitter' => $this->twitter,
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
