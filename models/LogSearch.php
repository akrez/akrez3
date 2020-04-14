<?php

namespace app\models;

use Yii;
use app\components\Helper;

/**
 * This is the model class for table "search".
 *
 * @property int $id
 * @property string|null $api_version
 * @property string|null $blog_name
 * @property string|null $created_date
 * @property string|null $created_time
 * @property int|null $user_id
 * @property string|null $user_agent
 * @property string|null $ip
 * @property string|null $category_id
 * @property string|null $params
 */
class LogSearch extends ActiveRecord
{

    public static function log($params)
    {
        $template = [
            'api_version' => null,
            'blog_name' => null,
            'created_date' => null,
            'created_time' => null,
            'user_id' => null,
            'user_agent' => null,
            'ip' => null,
            'category_id' => null,
            'params' => null,
        ];
        $data = Helper::templatedArray($template, $params);
        return Yii::$app->dbLog->createCommand()->insert(self::tableName(), $data)->execute();
    }

    public static function statSummary($blogName, $createdDateFrom)
    {
        $tableName = self::tableName();
        return Yii::$app->dbLog->createCommand("
            SELECT
                IF(`category_id` IS NULL, 0, 1) AS have_action_primary,
                `created_date`,
                COUNT(`id`) AS cnt
            FROM
                `$tableName`
            WHERE
                (`blog_name` = :blog_name) AND :created_date_from <= `created_date`
            GROUP BY
                have_action_primary,
                `created_date`
        ", [':blog_name' => $blogName, ':created_date_from' => $createdDateFrom])->queryAll();
    }

    public static function tableName()
    {
        return 'search';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLog');
    }

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id'], 'integer'],
            [['api_version'], 'string', 'max' => 5],
            [['blog_name'], 'string', 'max' => 31],
            [['created_date', 'created_time'], 'string', 'max' => 11],
            [['user_agent'], 'string', 'max' => 2047],
            [['ip'], 'string', 'max' => 17],
            [['category_id'], 'string', 'max' => 63],
            [['params'], 'string', 'max' => 8192],
            [['id'], 'unique'],
        ];
    }

}
