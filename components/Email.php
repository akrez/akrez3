<?php

namespace app\components;

use Exception;
use Yii;
use yii\base\Component;

class Email extends Component
{

    private static function send($to, $subject, $view, $params)
    {
        try {
            return Yii::$app->mailer
                            ->compose($view, $params)
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

}
