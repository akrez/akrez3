<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\CategorySearch;
use app\models\PackageSearch;
use app\models\ProductSearch;
use app\models\Category;
use app\models\Product;
use app\models\Status;
use Yii;
use app\components\WizardController;

class ProductController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Product(),
            'searchModel' => new ProductSearch(),
            'parentSearchModel' => new CategorySearch(),
            'parentModel' => new Category(),
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

    public function actionIndex($parent_id)
    {
        $this->wizard->findParentModel($parent_id);
        $this->wizard->newModel->status = Status::STATUS_DISABLE;
        $isSuccessfull = $this->wizard->create(Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
            'category_id' => $parent_id,
        ]);
        if ($isSuccessfull) {
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionUpdate($parent_id, $id)
    {
        $this->wizard->findParentModel($parent_id);
        $isSuccessfull = $this->wizard->update($id, Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
            'category_id' => $parent_id,
        ]);
        if ($isSuccessfull) {
            return $this->redirect(AdminHelper::url(['index', 'parent_id' => $parent_id]));
        }
        return $this->render('update', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionStatus($parent_id, $id, $status)
    {
        $this->wizard->findModel($id);
        $this->wizard->findParentModel($parent_id);
        $this->wizard->model->status = $status;
        $this->wizard->model->save();
        $redirectUrl = AdminHelper::url(['product/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionRemove($parent_id, $id)
    {
        $packages = PackageSearch::userValidQuery()->where(['product_id' => $id])->all();
        if ($packages) {
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($packages), 'child' => Yii::t('app', 'Package'), 'parent' => Yii::t('app', 'Product')]);
            Yii::$app->session->setFlash('danger', $msg);
        } else {
            $this->wizard->remove($id);
        }
        $redirectUrl = AdminHelper::url(['product/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

}
