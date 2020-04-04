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

    public function rules()
    {
        return [
            [['!product_id', '!field_id', 'value'], 'required'],
        ];
    }

}
