<?php

namespace app\components;

use Exception;
use Yii;
use yii\base\Component;

class Email extends Component
{

    public static $from;

    public function init()
    {
        parent::init();
        self::$from = ['akrezing@gmail.com' => APP_NAME];
    }

    private static function send($to, $subject, $view, $params)
    {
        try {
            return Yii::$app->mailer
                            ->compose($view, $params)
                            ->setFrom(self::$from)
                            ->setTo($to)
                            ->setSubject($subject)
                            ->send();
        } catch (Exception $e) {
            
        }
        return false;
    }

    public static function resetPasswordRequest($user)
    {
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send($user->email, $title, 'resetPasswordRequest', [
                    '_title' => $title,
                    'user' => $user,
        ]);
    }

    public static function customerResetPasswordRequest($customer, $blog)
    {
        self::$from = ['akrezing@gmail.com' => $blog->title];
        $title = Yii::t('app', 'ResetPasswordRequest');
        return self::send($customer->email, $title, 'customerResetPasswordRequest', [
                    '_title' => $title,
                    'customer' => $customer,
                    'blog' => $blog,
        ]);
    }

}
