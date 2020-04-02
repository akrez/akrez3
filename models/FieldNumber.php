<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field_number".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property float $value
 * @property int $product_id
 * @property int $field_id
 *
 * @property Field $field
 * @property Product $product
 */
class FieldNumber extends Fields
{

    public static function tableName()
    {
        return 'field_number';
    }

    public function rules()
    {
        return array_merge([
            [['value'], 'number'],
        ], parent::rules());
    }

}
