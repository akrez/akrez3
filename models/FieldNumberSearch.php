<?php

namespace app\models;

use app\models\FieldNumber;

class FieldNumberSearch extends FieldNumber
{

    public static function userValidQuery($id = null)
    {
        $query = FieldNumber::find();
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }

}
