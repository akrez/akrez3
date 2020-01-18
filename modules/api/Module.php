<?php

namespace app\modules\api;

use Yii;
use yii\web\Response;

class Module extends \yii\base\Module
{

    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();
        Yii::$app->user->loginUrl = null;
        Yii::$app->user->enableSession = false;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->charset = 'UTF-8';
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $response = $event->sender;
            if ($response->data !== null && $response->statusCode !== 200) {
                $code = $event->sender->statusCode;
                $event->sender->data = ($code == 200 || YII_ENV_DEV ? (array) $event->sender->data : []);
                $event->sender->data['code'] = $code;
            }
        });
    }

}
