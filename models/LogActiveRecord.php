<?php

namespace app\models;

use yii\db\ActiveRecord as BaseActiveRecord;

class LogActiveRecord extends BaseActiveRecord
{

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'api_version' => 'نسخه API',
            'blog_name' => 'Blog Name',
            'created_date' => 'تاریخ بازدید',
            'created_time' => 'ساعت بازدید',
            'user_id' => 'کاربر',
            'user_agent' => 'User Agent',
            'ip' => 'IP',
            'category_id' => 'دسته‌بندی',
            'params' => 'پارامترها',
        ];
    }

}
