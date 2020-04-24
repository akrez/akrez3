<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\components\WizardController;
use app\models\Category;
use app\models\CategorySearch;
use app\models\ProductSearch;
use app\models\Status;
use Yii;

class CategoryController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Category(),
            'searchModel' => new CategorySearch(),
        ]);
    }

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'update', 'status', 'remove'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return array_merge_recursive($behaviors, parent::behaviors());
    }

    public function actionIndex()
    {
        $this->wizard->newModel->status = Status::STATUS_DISABLE;
        $isSuccessfull = $this->wizard->create(Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
        ]);
        if ($isSuccessfull) {
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionUpdate($id)
    {
        $isSuccessfull = $this->wizard->update($id, Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
        ]);
        if ($isSuccessfull) {
            return $this->redirect(AdminHelper::url(['index']));
        }
        return $this->render('update', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionStatus($id, $status)
    {
        $this->wizard->findModel($id);
        $this->wizard->model->status = $status;
        $this->wizard->model->save();
        $redirectUrl = AdminHelper::url(['category/index']);
        return $this->redirect($redirectUrl);
    }

    public function actionRemove($id)
    {
        $products = ProductSearch::userValidQuery()->where(['category_id' => $id])->all();
        if ($products) {
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($products), 'child' => Yii::t('app', 'Product'), 'parent' => Yii::t('app', 'Category')]);
            Yii::$app->session->setFlash('danger', $msg);
        } else {
            $this->wizard->remove($id);
        }
        $redirectUrl = AdminHelper::url(['category/index']);
        return $this->redirect($redirectUrl);
    }

}
