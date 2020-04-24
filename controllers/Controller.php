<?php

namespace app\controllers;

use app\models\Blog;
use Yii;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Controller extends BaseController
{

    /** @var app\components\WizardController */
    public $wizard;

    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'denyCallback' => function ($rule, $action) {
                    if (Yii::$app->user->isGuest) {
                        Yii::$app->user->setReturnUrl(Url::current());
                        return $this->redirect(['/site/signin']);
                    }
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                }
            ],
        ];
        return array_merge_recursive(parent::behaviors(), $behaviors);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->controller->id == 'site') {
            //do nothing
        } else {
            $blogName = Yii::$app->request->get('_blog', null);
            $userId = Yii::$app->user->getId();
            $blog = Blog::findBlogForAdmin($blogName, $userId);
            if ($blog == null) {
                throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
            }
            Yii::$app->blog->setIdentity($blog);
        }

        return true;
    }

}
