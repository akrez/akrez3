<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\InvoiceSearch;
use app\models\Basket;
use app\models\Invoice;
use app\models\Package;
use app\models\Product;
use Yii;
use yii\helpers\ArrayHelper;
use app\components\WizardController;

class InvoiceController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Invoice(),
            'searchModel' => new InvoiceSearch(),
        ]);
    }

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'remove', 'verify', 'view'],
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
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionView($id)
    {
        $invoice = $this->wizard->findModel($id);

        $baskets = Basket::find()->where(['invoice_id' => $invoice->id])->asArray()->all();
        $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
        $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();

        return $this->render('view', [
                    'invoice' => $invoice,
                    'list' => [
                        'baskets' => $baskets,
                        'packages' => $packages,
                        'products' => $products,
                    ]
        ]);
    }

    public function actionRemove($id)
    {
        $model = $this->wizard->findModel($id);
        if ($model->status == Invoice::STATUS_UNVERIFIED) {
            $model->status = Invoice::STATUS_ADMIN_DELETED_UNVERIFIED;
            $model->save(false);
        } elseif ($model->status == Invoice::STATUS_VERIFIED) {
            $model->status = Invoice::STATUS_ADMIN_DELETED_VERIFIED;
            $model->save(false);
        }
        $redirectUrl = AdminHelper::url(['invoice/index']);
        return $this->redirect($redirectUrl);
    }

    public function actionVerify($id)
    {
        $model = $this->wizard->findModel($id);
        if ($model->status == Invoice::STATUS_UNVERIFIED) {
            $model->status = Invoice::STATUS_VERIFIED;
            $model->save(false);
        }
        $redirectUrl = AdminHelper::url(['invoice/index']);
        return $this->redirect($redirectUrl);
    }

}
