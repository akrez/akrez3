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
            $statusCode = $event->sender->statusCode;
            if ($statusCode != 200 && !YII_DEBUG) {
                $event->sender->data = ['code' => $statusCode];
            } elseif (isset($event->sender->data['code']) && !YII_DEBUG) {
                $event->sender->data = ['code' => $event->sender->data['code']];
            } else {
                $event->sender->data = (array) $event->sender->data;
                $event->sender->data['code'] = $statusCode;
            }
        });
    }

}
