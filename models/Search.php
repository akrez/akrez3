<?php

namespace app\models;

use app\components\Helper;

class Search extends Model
{

    public $field;
    public $type;
    public $category_id;
    public $operation;
    public $widget;
    //
    public $_value;
    //
    public $value;
    public $values;
    public $value_min;
    public $value_max;

    public function rules()
    {
        return [
            [['!field', '!type', 'operation', '_value', 'widget',], 'required'],
            [['value', 'values', 'value_min', 'value_max'], 'safe'],
            [['operation'], 'in', 'range' => function() {
                    return array_keys(FieldList::getTypeOpertaions($this->type));
                }],
            [['widget'], 'in', 'range' => function() {
                    return array_keys(FieldList::getTypeWidgets($this->type));
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
        //
        $this->_value = null;
        if (in_array($this->operation, FieldList::getPluralOperations())) {
            $this->value = null;
            $this->values = $this->filterByType($this->values, $this->type, true);
            $this->value_min = null;
            $this->value_max = null;
            $this->_value = $this->values;
        } elseif (in_array($this->operation, FieldList::getMinMaxOperations())) {
            $this->value = null;
            $this->values = null;
            if (strlen($this->value_min) > 0 && strlen($this->value_max) > 0) {
                $this->value_min = $this->filterByType($this->value_min, $this->type);
                $this->value_max = $this->filterByType($this->value_max, $this->type);
                $this->_value = [0 => $this->value_min, 1 => $this->value_max,];
            } else {
                $this->value_min = null;
                $this->value_max = null;
                $this->_value = [];
            }
        } else {
            $this->value = $this->filterByType($this->value, $this->type);
            $this->values = null;
            $this->value_min = null;
            $this->value_max = null;
            $this->_value = $this->value;
        }
        //
        return true;
    }

    private function filterByType($value, $type, $isArray = false)
    {
        try {
            if ($isArray) {
                $value = (array) $value;
                if (empty($isArray)) {
                    return [];
                }
            } else {
                if (!strlen($value) > 0) {
                    return null;
                }
            }
            if ($type == FieldList::TYPE_NUMBER || $type == FieldList::TYPE_BOOLEAN) {
                if ($isArray) {
                    array_map('floatval', $value);
                    return $value;
                }
                return floatval($value);
            } elseif ($type == FieldList::TYPE_STRING) {
                if ($isArray) {
                    array_map('strval', $value);
                    return $value;
                }
                return strval($value);
            }
        } catch (Exception $ex) {
            
        }
        return null;
    }

}
