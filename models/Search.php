<?php

namespace app\models;

use app\components\Helper;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $updated_at
 * @property int $created_at
 * @property string $status
 * @property string $title
 * @property double $price_min
 * @property double $price_max
 * @property string $image
 * @property int $category_id
 * @property string $blog_name
 *
 * @property Gallery[] $galleries
 * @property Package[] $packages
 * @property Category $category
 * @property Blog $blogName
 * @property ProductField[] $productFields
 */
class Search extends Model
{

    public $field;
    public $type;
    public $category_id;
    public $operation;
    public $value;
    public $widget;

    public function rules()
    {
        return [
            [['!field', '!type', 'operation', 'value', 'widget',], 'required'],
            [['operation'], 'in', 'range' => function() {
                    return array_keys(FieldList::getTypeOpertaions($this->type));
                }],
            [['widget'], 'in', 'range' => function() {
                    return array_keys(FieldList::getTypeWidgets($this->type));
                }],
            [['value'], function ($attribute, $params, $validator) {
                    if ($this->operation == FieldList::OPERATION_BETWEEN) {
                        if (!isset($this->value [0]) || !isset($this->value [1])) {
                            $this->addError($attribute, 'The value must contain 2 value');
                        }
                    }
                }],
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        //
        if (empty($this->widget)) {
            $this->widget = FieldList::getDefaultWidgetOfType($this->type);
        }
        if (in_array($this->operation, FieldList::getPluralOperations())) {
            $this->value = Helper::normalizeArray($this->value, true);
            if ($this->type == FieldList::TYPE_NUMBER || $this->type == FieldList::TYPE_BOOLEAN) {
                array_map('floatval', $this->value);
            } else {
                array_map('strval', $this->value);
            }
        } else {
            if ($this->type == FieldList::TYPE_NUMBER || $this->type == FieldList::TYPE_BOOLEAN) {
                floatval($this->value);
            } else {
                strval($this->value);
            }
        }
        //
        return true;
    }

}
