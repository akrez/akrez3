<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\components\WizardController;
use app\models\BasketSearch;
use app\models\CategorySearch;
use app\models\Package;
use app\models\PackageSearch;
use app\models\Product;
use app\models\ProductSearch;
use app\models\Status;
use Yii;
use yii\db\Exception;

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
            $this->afterUpdate();
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams, [
                            'garanties' => $garanties,
        ]));
    }

    public function actionRemove($parent_id, $id)
    {
        $this->wizard->findParentModel($parent_id);
        $this->wizard->remove($id);
        $this->afterUpdate();
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionStatus($parent_id, $id, $status)
    {
        $this->wizard->findParentModel($parent_id);
        $this->wizard->findModel($id);
        $this->wizard->model->status = ($status == Status::STATUS_DISABLE ? Status::STATUS_DISABLE : Status::STATUS_ACTIVE);
        $this->wizard->model->save();
        $this->afterUpdate();
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionUpdatePrice($parent_id, $id)
    {
        $redirectUrl = AdminHelper::url(['package/index', 'parent_id' => $parent_id]);
        //
        if ($price = Yii::$app->request->post('price')) {
            $this->wizard->findParentModel($parent_id);
            $this->wizard->findModel($id);
            $model = new Package();
            $model->load($this->wizard->model->attributes, '');
            $model->price = $price;
            $model->product_id = $this->wizard->model->product_id;
            $model->blog_name = $this->wizard->model->blog_name;
            if (!$model->save()) {
                return $this->redirect($redirectUrl);
            }
            $this->wizard->model->status = Status::STATUS_DELETED;
            if (!$this->wizard->model->save()) {
                return $this->redirect($redirectUrl);
            }
            $this->afterUpdate();
        }
        //
        return $this->redirect($redirectUrl);
    }

    private function afterUpdate()
    {
        //setProductPriceRange
        $priceRange = (array) PackageSearch::userValidQuery()->select(['price_min' => 'MIN(price)', 'price_max' => 'MAX(price)'])->where(['product_id' => $this->wizard->parentModel->id])->andWhere(['status' => Status::STATUS_ACTIVE])->asArray()->one() + ['price_min' => null, 'price_max' => null];
        $this->wizard->parentModel->price_min = ($priceRange['price_min'] === null ? null : doubleval($priceRange['price_min']));
        $this->wizard->parentModel->price_max = ($priceRange['price_max'] === null ? null : doubleval($priceRange['price_max']));
        $this->wizard->parentModel->save();
        //updateCategoryPriceAndGuaranty
        $category = CategorySearch::userValidQuery($this->wizard->parentModel->category_id)->one();
        $categoryProductQuery = ProductSearch::userValidQuery()->select('id')->where(['category_id' => $category->id]);
        //
        $categoryPriceRange = (array) PackageSearch::userValidQuery()->select(['price_min' => 'MIN(price)', 'price_max' => 'MAX(price)'])->where(['product_id' => $categoryProductQuery])->andWhere(['status' => Status::STATUS_ACTIVE])->asArray()->one() + ['price_min' => null, 'price_max' => null];
        $categoryGaranties = PackageSearch::userValidQuery()->select('guaranty')->where(['product_id' => $categoryProductQuery])->andWhere(['status' => [Status::STATUS_ACTIVE, Status::STATUS_DISABLE,]])->asArray()->column();
        //
        $category->price_min = ($categoryPriceRange['price_min'] === null ? null : doubleval($categoryPriceRange['price_min']));
        $category->price_max = ($categoryPriceRange['price_max'] === null ? null : doubleval($categoryPriceRange['price_max']));
        $category->garanties = $categoryGaranties;
        $category->save();
    }

}
