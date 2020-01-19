<?php

namespace app\modules\api\controllers;

use app\models\Blog;
use Yii;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Controller extends BaseController
{

    public function defaultBehaviors($rules = [])
    {
        return [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => $rules,
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        Yii::$app->user->setReturnUrl(Url::current());
                        return $this->redirect(['site/signin']);
                    }
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                }
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $blogName = Yii::$app->request->get('_blog', null);
        $blog = Blog::findBlogForClient($blogName);
        if ($blog == null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        Yii::$app->blog->setIdentity($blog);

        return true;
    }

}
