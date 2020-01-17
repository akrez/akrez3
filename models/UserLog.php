<?php

namespace app\models;

/**
 * This is the model class for table "{{%user_log}}".
 *
 * @property int $id
 * @property string $created_at
 * @property string $type
 * @property int $blog_name
 */
class UserLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_log}}';
    }
}
