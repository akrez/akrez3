<?php

namespace app\controllers;

use app\models\CategorySearch;
use app\models\ProductSearch;
use Yii;
use yii\helpers\Url;

class DefaultController extends Controller
{

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'update', 'delete'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return array_merge_recursive($behaviors, parent::behaviors());
    }

    public function actionIndex($id = null)
    {
        return $this->render('index');
    }

    public function actionUpdate()
    {
        $model = Yii::$app->blog->getIdentity();
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->getId();
            if ($model->save()) {
                $url = Url::to(['default/index', '_blog' => $model->name]);
                return $this->redirect($url);
            }
        }

        return $this->render('form', [
                    'model' => $model,
        ]);
    }

    public function actionDelete()
    {
        $blog = Yii::$app->blog->getIdentity();
        $products = ProductSearch::userValidQuery()->where(['blog_name' => $blog->name])->all();
        if ($products) {
            $url = Url::to(['default/update', '_blog' => $blog->name]);
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($products), 'child' => Yii::t('app', 'Product'), 'parent' => Yii::t('app', 'Blog')]);
            Yii::$app->session->setFlash('danger', $msg);
            return $this->redirect($url);
        }
        $category = CategorySearch::userValidQuery()->where(['blog_name' => $blog->name])->all();
        if ($category) {
            $url = Url::to(['default/update', '_blog' => $blog->name]);
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($category), 'child' => Yii::t('app', 'Category'), 'parent' => Yii::t('app', 'Blog')]);
            Yii::$app->session->setFlash('danger', $msg);
            return $this->redirect($url);
        }
        $blog->delete();
        return $this->redirect(['site/blogs']);
    }

}
