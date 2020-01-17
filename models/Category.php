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

    public static function tableName()
    {
        return 'category';
    }

    public function rules()
    {
        return [
            [['status', 'title'], 'required'],
            [['title'], 'string', 'max' => 64],
            [['garanties'], 'safe'],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            [['!blog_name'], 'required'],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();

        $arrayParams = (array) Json::decode($this->params) + [
            'garanties' => null,
        ];
        $this->garanties = $arrayParams['garanties'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->garanties = Helper::normalizeArray($this->garanties);
        $this->params = [
            'garanties' => $this->garanties,
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

}
