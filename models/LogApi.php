<?php

namespace app\models;

use Yii;
use app\components\Helper;

/**
 * This is the model class for table "api".
 *
 * @property int $id
 * @property string|null $api_version
 * @property string|null $blog_name
 * @property string|null $created_at
 * @property int|null $user_id
 * @property string|null $user_agent
 * @property string|null $ip
 * @property string|null $action
 * @property string|null $action_primary
 */
class LogApi extends ActiveRecord
{

    public static function log($params)
    {
        $template = [
            'api_version' => null,
            'blog_name' => null,
            'created_at' => null,
            'user_id' => null,
            'user_agent' => null,
            'ip' => null,
            'action' => null,
            'action_primary' => null,
        ];
        $data = Helper::templatedArray($template, $params);
        return Yii::$app->dbLog->createCommand()->insert('api', $data)->execute();
    }

    public static function tableName()
    {
        return 'api';
    }

    public static function getDb()
    {
        return Yii::$app->get('dbLog');
    }

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['api_version'], 'string', 'max' => 5],
            [['blog_name'], 'string', 'max' => 31],
            [['created_at'], 'string', 'max' => 20],
            [['user_agent'], 'string', 'max' => 2047],
            [['ip'], 'string', 'max' => 17],
            [['action', 'action_primary'], 'string', 'max' => 63],
        ];
    }

}
