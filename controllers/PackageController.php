<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\CategorySearch;
use app\models\PackageSearch;
use app\models\ProductSearch;
use app\models\Package;
use app\models\Product;
use app\models\Status;
use Yii;
use yii\db\Exception;
use app\components\WizardController;
use app\models\BasketSearch;

class PackageController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Package(),
            'searchModel' => new PackageSearch(),
            'parentSearchModel' => new ProductSearch(),
            'parentModel' => new Product(),
        ]);
    }

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'remove', 'status', 'update-price'],
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
        try {
            $garanties = CategorySearch::userValidQuery($this->wizard->parentModel->category_id)->one()->getGarantiesList();
        } catch (Exception $ex) {
            $garanties = [];
        }
        $isSuccessfull = $this->wizard->create(Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
            'product_id' => $parent_id,
        ]);
        if ($isSuccessfull) {
            $this->setProductPriceRange();
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams, [
                            'garanties' => $garanties,
        ]));
    }

    public function actionRemove($parent_id, $id)
    {
        $baskets = BasketSearch::userValidQuery()->where([
                    'AND',
                    ['package_id' => $id],
                    ['NOT', ['invoice_id' => null]],
                ])->all();
        if ($baskets) {
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => count($baskets), 'child' => Yii::t('app', 'Baskets'), 'parent' => Yii::t('app', 'Package')]);
            Yii::$app->session->setFlash('danger', $msg);
        } else {
            $this->wizard->remove($id);
        }
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionStatus($parent_id, $id, $status)
    {
        $this->wizard->findModel($id);
        $this->wizard->model->status = ($status == Status::STATUS_DISABLE ? Status::STATUS_DISABLE : Status::STATUS_ACTIVE);
        $this->wizard->model->save();
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionUpdatePrice($parent_id, $id)
    {
        if ($price = Yii::$app->request->post('price')) {
            $this->wizard->findModel($id);
            $model = new Package();
            $model->load($this->wizard->model->attributes, '');
            $model->price = $price;
            $model->product_id = $this->wizard->model->product_id;
            $model->blog_name = $this->wizard->model->blog_name;
            if ($model->save()) {
                $this->wizard->model->status = Status::STATUS_DELETED;
                $this->wizard->model->save();
            } else {
                ed($model->errors);
            }
        }
        //
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    private function setProductPriceRange()
    {
        $priceRange = PackageSearch::userValidQuery()->select(['price_min' => 'MIN(price)', 'price_max' => 'MAX(price)'])->where(['product_id' => $this->wizard->parentModel->id])->asArray()->one();
        $priceRange = $priceRange + ['price_min' => null, 'price_max' => null];
        if ($this->wizard->parentModel->price_min != $priceRange['price_min'] || $this->wizard->parentModel->price_max != $priceRange['price_max']) {
            $this->wizard->parentModel->price_min = $priceRange['price_min'];
            $this->wizard->parentModel->price_max = $priceRange['price_max'];
            $this->wizard->parentModel->save();
        }
    }

}
