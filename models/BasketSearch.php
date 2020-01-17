<?php

namespace app\models;

use Yii;

class BasketSearch extends FieldString
{

    public static function userValidQuery($id = null)
    {
        $query = Basket::find();
        $query->andWhere(['blog_name' => Yii::$app->blog->name(),]);
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

}
