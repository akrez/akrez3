<?php

namespace app\models;

use app\components\Helper;
use yii\helpers\Json;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property int $updated_at
 * @property string $status
 * @property string $title
 * @property string $params
 * @property string $blog_name
 *
 * @property Blog $blogName
 * @property Field[] $fields
 * @property Product[] $products
 */
class Category extends ActiveRecord
{

    public $garanties;
    public $price_min;
    public $price_max;

    public static function tableName()
    {
        return 'category';
    }

    public function rules()
    {
        return [
            [['status', 'title'], 'required'],
            [['title'], 'string', 'max' => 64],
            [['garanties', 'price_min', 'price_max'], 'safe'],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            [['!blog_name'], 'required'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        $arrayParams = (array) Json::decode($this->params) + [
            'garanties' => null,
            'price_min' => null,
            'price_max' => null,
        ];
        $this->garanties = $arrayParams['garanties'];
        $this->price_min = $arrayParams['price_min'];
        $this->price_max = $arrayParams['price_max'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->garanties = Helper::normalizeArray($this->garanties);
        $this->params = [
            'garanties' => $this->garanties,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
        ];
        $this->params = Json::encode($this->params);

        return true;
    }

    public function getGarantiesList()
    {
        try {
            return explode(',', $this->garanties);
        } catch (Exception $ex) {
            
        }
        return [];
    }

    public function export()
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'title' => $this->title,
            'blog_name' => $this->blog_name,
            'garanties' => $this->garanties,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
        ];
    }

}
