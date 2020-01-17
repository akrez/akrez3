<?php

namespace app\models;

/**
 * This is the model class for table "product_field".
 *
 * @property int $id
 * @property int $updated_at
 * @property string $value
 * @property int $product_id
 * @property int $field_id
 *
 * @property Blog $blogName
 * @property Product $product
 * @property Field $field
 */
class Fields extends ActiveRecord
{

    public $field;

    public function getFieldAttribute($attribute)
    {
        if ($this->field) {
            return $this->field->$attribute;
        }
        return null;
    }

    public function rules()
    {
        return [
            [['!product_id', '!field_id', '!field', 'value'], 'required'],
        ];
    }

}
