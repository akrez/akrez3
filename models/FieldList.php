<?php

namespace app\models;

use Yii;

class FieldList extends Model
{

    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';

    public static function typeList()
    {
        return [
            self::TYPE_STRING => Yii::t('app', 'string'),
            self::TYPE_NUMBER => Yii::t('app', 'number'),
            self::TYPE_BOOLEAN => Yii::t('app', 'boolean'),
        ];
    }

    public static function typeLabel($type)
    {
        $list = self::typeList();
        return (isset($list[$type]) ? $list[$type] : null);
    }

    const FILTER_STRING = 'filter_string';
    const FILTER_NUMBER = 'filter_number';
    const FILTER_2STATE = 'filter_2state';
    const FILTER_3STATE = 'filter_3state';
    const FILTER_SINGLE = 'filter_single';
    const FILTER_MULTI = 'filter_multi';

    public static function filterList()
    {
        return [
            self::TYPE_STRING => [
                self::FILTER_STRING => Yii::t('app', 'filter_string'),
                self::FILTER_SINGLE => Yii::t('app', 'filter_single'),
                self::FILTER_MULTI => Yii::t('app', 'filter_multi'),
            ],
            self::TYPE_NUMBER => [
                self::FILTER_NUMBER => Yii::t('app', 'filter_number'),
                self::FILTER_SINGLE => Yii::t('app', 'filter_single'),
                self::FILTER_MULTI => Yii::t('app', 'filter_multi'),
            ],
            self::TYPE_BOOLEAN => [
                self::FILTER_2STATE => Yii::t('app', 'filter_2state'),
                self::FILTER_3STATE => Yii::t('app', 'filter_3state'),
            ],
        ];
    }

    public static function getTypeFilter($type)
    {
        $list = self::filterList();
        return (isset($list[$type]) ? $list[$type] : []);
    }

    public static function opertaionList()
    {
        return [
            self::FILTER_STRING => [
                'LIKE' => Yii::t('app', 'LIKE'),
                'NOT LIKE' => Yii::t('app', 'NOT LIKE'),
                '=' => Yii::t('app', 'EQUAL'),
                '<>' => Yii::t('app', 'NOT EQUAL'),
            ],
            self::FILTER_NUMBER => [
                '>' => Yii::t('app', 'BIGGER THAN'),
                '<' => Yii::t('app', 'SMALLER THAN'),
                '=' => Yii::t('app', 'EQUAL'),
                '<>' => Yii::t('app', 'NOT EQUAL'),
            ],
            self::FILTER_2STATE => [
                '=' => Yii::t('app', 'BE'),
                '<>' => Yii::t('app', 'NOT BE'),
            ],
            self::FILTER_3STATE => [
                '=' => Yii::t('app', 'BE'),
                '<>' => Yii::t('app', 'NOT BE'),
            ],
            self::FILTER_SINGLE => [
                'IN' => Yii::t('app', 'IN'),
                'NOT IN' => Yii::t('app', 'NOT IN'),
            ],
            self::FILTER_MULTI => [
                'IN' => Yii::t('app', 'IN'),
                'NOT IN' => Yii::t('app', 'NOT IN'),
            ],
        ];
    }

    public static function getFilterOpertaion($type)
    {
        $list = self::opertaionList();
        return (isset($list[$type]) ? $list[$type] : []);
    }

}
