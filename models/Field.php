<?php

namespace app\models;

use yii\helpers\Json;
use app\components\Helper;

/**
 * This is the model class for table "field".
 *
 * @property int $id
 * @property int $updated_at
 * @property string $title
 * @property string $type
 * @property string $filter
 * @property int $seq
 * @property string $params
 * @property int $category_id
 *
 * @property Category $category
 * @property ProductField[] $productFields
 */
class Field extends ActiveRecord
{

    public $unit;
    public $value_max;
    public $label_no;
    public $label_yes;
    public $options;

    public static function tableName()
    {
        return 'field';
    }

    public function rules()
    {
        $rules = [
            [['seq'], 'integer'],
            [['in_summary'], 'default', 'value' => 0],
            [['value_max'], 'number'],
            [['title', 'type'], 'required'],
            [['title'], 'string', 'max' => 64],
            [['unit', 'label_no', 'label_yes'], 'string'],
            [['options'], 'safe'],
            [['type'], 'in', 'range' => array_keys(FieldList::typeList())],
            [['in_summary'], 'boolean'],
            [['filter'], 'in', 'skipOnError' => true, 'range' => function ($model, $attribute) {
                    return array_keys(FieldList::getTypeFilter($model->type));
                }],
        ];

        if (!$this->isNewRecord) {
            $rules = array_merge($rules, [[['!type'], 'string', 'max' => 15]]);
        }

        return $rules;
    }

    public function afterFind()
    {
        parent::afterFind();
        $arrayParams = (array) Json::decode($this->params) + [
            'unit' => null,
            'value_max' => null,
            'label_no' => null,
            'label_yes' => null,
            'options' => null,
        ];

        $this->unit = $arrayParams['unit'];
        $this->value_max = $arrayParams['value_max'];
        $this->label_no = $arrayParams['label_no'];
        $this->label_yes = $arrayParams['label_yes'];
        $this->options = $arrayParams['options'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->value_max = Helper::formatDecimal($this->value_max);
        $this->options = Helper::normalizeArray($this->options);

        $this->params = [
            'unit' => $this->unit,
            'value_max' => $this->value_max,
            'label_no' => $this->label_no,
            'label_yes' => $this->label_yes,
            'options' => $this->options,
        ];

        $this->params = Json::encode($this->params);
        return true;
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'filter' => $this->filter,
            'seq' => $this->seq,
            'in_summary' => $this->in_summary,
            'category_id' => $this->category_id,
            'unit' => $this->unit,
            'value_max' => $this->value_max,
            'label_no' => $this->label_no,
            'label_yes' => $this->label_yes,
            'options' => $this->options,
        ];
    }

}
