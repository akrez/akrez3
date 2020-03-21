<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "api".
 *
 * @property int $id
 * @property string $api_version
 * @property string $blog_name
 * @property string $created_at
 * @property int|null $user_id
 * @property string|null $user_agent
 * @property string|null $ip
 * @property string $action
 * @property string|null $action_primary
 */
class LogApi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'api';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbLog');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['api_version', 'blog_name', 'created_at', 'action'], 'required'],
            [['user_id'], 'integer'],
            [['api_version'], 'string', 'max' => 5],
            [['blog_name'], 'string', 'max' => 31],
            [['created_at'], 'string', 'max' => 20],
            [['user_agent'], 'string', 'max' => 2047],
            [['ip'], 'string', 'max' => 17],
            [['action', 'action_primary'], 'string', 'max' => 63],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'api_version' => 'Api Version',
            'blog_name' => 'Blog Name',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
            'user_agent' => 'User Agent',
            'ip' => 'Ip',
            'action' => 'Action',
            'action_primary' => 'Action Primary',
        ];
    }
}
