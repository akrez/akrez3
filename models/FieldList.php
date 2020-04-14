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

    ////////////////////////////////////////////////////////

    const OPERATION_BETWEEN = 'BETWEEN';

    public static function typesOpertaionsList()
    {
        return [
            self::TYPE_STRING => [
                'LIKE' => Yii::t('app', 'LIKE'),
                'NOT LIKE' => Yii::t('app', 'NOT LIKE'),
                '=' => Yii::t('app', 'EQUAL'),
                '<>' => Yii::t('app', 'NOT EQUAL'),
                'IN' => Yii::t('app', 'IN'),
                'NOT IN' => Yii::t('app', 'NOT IN'),
            ],
            self::TYPE_NUMBER => [
                '>=' => Yii::t('app', 'BIGGER THAN'),
                '<=' => Yii::t('app', 'SMALLER THAN'),
                '=' => Yii::t('app', 'EQUAL'),
                '<>' => Yii::t('app', 'NOT EQUAL'),
                'IN' => Yii::t('app', 'IN'),
                'NOT IN' => Yii::t('app', 'NOT IN'),
                self::OPERATION_BETWEEN => Yii::t('app', 'BETWEEN'),
            ],
            self::TYPE_BOOLEAN => [
                '=' => Yii::t('app', 'BE'),
                '<>' => Yii::t('app', 'NOT BE'),
            ],
        ];
    }

    public static function getTypeOpertaions($type)
    {
        $list = self::typesOpertaionsList();
        return (isset($list[$type]) ? $list[$type] : []);
    }

    ////////////////////////////////////////////////////////

    const WIDGET_BETWEEN = 'BETWEEN';
    const WIDGET_BIGGER = '>=';
    const WIDGET_SMALLER = '<=';

    public static function typesWidgetsList()
    {
        return [
            self::TYPE_STRING => [
                'LIKE' => Yii::t('app', 'widget_like'),
                'COMBO' => Yii::t('app', 'widget_combo'),
                'NOT LIKE' => Yii::t('app', 'widget_not_like'),
                '=' => Yii::t('app', 'widget_equal'),
                '<>' => Yii::t('app', 'widget_not_equal'),
                'SINGLE' => Yii::t('app', 'widget_single'),
                'MULTI' => Yii::t('app', 'widget_multi'),
            ],
            self::TYPE_NUMBER => [
                self::WIDGET_BETWEEN => Yii::t('app', 'widget_between'),
                'COMBO' => Yii::t('app', 'widget_combo'),
                self::WIDGET_BIGGER => Yii::t('app', 'widget_bigger'),
                self::WIDGET_SMALLER => Yii::t('app', 'widget_smaller'),
                '=' => Yii::t('app', 'widget_equal'),
                '<>' => Yii::t('app', 'widget_not_equal'),
                'SINGLE' => Yii::t('app', 'widget_single'),
                'MULTI' => Yii::t('app', 'widget_multi'),
            ],
            self::TYPE_BOOLEAN => [
                '2STATE' => Yii::t('app', 'widget_2state'),
                '3STATE' => Yii::t('app', 'widget_3state'),
            ],
        ];
    }

    public static function getTypeWidgets($type)
    {
        $list = self::typesWidgetsList();
        return (isset($list[$type]) ? $list[$type] : []);
    }

    public static function getDefaultWidgetOfType($type)
    {
        $list = self::getTypeWidgets($type);
        return key($list);
    }

    public static function getPluralOperations()
    {
        return ['IN', 'NOT IN', self::OPERATION_BETWEEN,];
    }

}
