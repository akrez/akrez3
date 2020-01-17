<?php
namespace app\models;

use Yii;
use ReflectionClass;
use yii\db\ActiveRecord as BaseActiveRecord;

class ActiveRecord extends BaseActiveRecord
{

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            if ($this->hasAttribute('created_at')) {
                $this->created_at = time();
            }
        }
        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = time();
        }
        return true;
    }

    public static function modelName()
    {
        $name = (new ReflectionClass(static::class))->getShortName();
        return Yii::t('app', $name);
    }

    public function attributeLabels()
    {
        return Model::attributeLabelsList();
    }
}
