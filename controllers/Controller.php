<?php

namespace app\controllers;

use app\models\Blog;
use app\models\Status;
use Yii;
use yii\helpers\Url;
use yii\web\Controller as BaseController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Controller extends BaseController
{

    public $wizard = null;
    public $successful = null;
    public $newModel = null;
    public $searchModel = null;
    public $parentModel = null;
    public $parentSearchModel = null;

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
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
            Yii::$app->blog->setIdentity($blog);
        }

        return true;
    }

}
