<?php

namespace app\models;

use app\models\FieldString;

class FieldStringSearch extends FieldString
{

    public static function userValidQuery($id = null)
    {
        $query = FieldString::find();
        $query->andFilterWhere(['id' => $id]);
        return $query;
    }
}
