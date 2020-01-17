<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field_string".
 *
 * @property int $id
 * @property int $updated_at
 * @property string $value
 * @property int $product_id
 * @property int $field_id
 *
 * @property Field $field
 * @property Product $product
 */
class FieldString extends Fields
{

    public static function tableName()
    {
        return 'field_string';
    }

    public function rules()
    {
        return array_merge([
            [['value'], 'safe'],
                ], parent::rules());
    }

}
