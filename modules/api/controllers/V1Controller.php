<?php

namespace app\modules\api\controllers;

use app\components\SingleSort;
use app\models\Basket;
use app\models\Category;
use app\models\Color;
use app\models\Customer;
use app\models\Field;
use app\models\FieldList;
use app\models\FieldNumber;
use app\models\FieldString;
use app\models\Gallery;
use app\models\Invoice;
use app\models\Package;
use app\models\Product;
use app\models\Province;
use app\models\Status;
use Yii;
use yii\base\DynamicModel;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class V1Controller extends Controller
{

    private static $_blog = false;
    private static $_categories = false;
    private static $_customer = false;

    public static function blog()
    {
        if (self::$_blog === false) {
            if (Yii::$app->blog->getIdentity()) {
                return self::$_blog = Yii::$app->blog->getIdentity()->info();
            }
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
        return self::$_blog;
    }

    public static function categories()
    {
        $blog = self::blog();
        if (self::$_categories === false) {
            self::$_categories = Category::find()->select(['title', 'id'])->where(['blog_name' => $blog['name'], 'status' => Status::STATUS_ACTIVE])->indexBy('id')->asArray()->column();
        }
        return self::$_categories;
    }

    public static function customer()
    {
        if (self::$_customer === false) {
            if (Yii::$app->customerApi->getIdentity()) {
                return self::$_customer = Yii::$app->customerApi->getIdentity()->info();
            }
            return self::$_customer = null;
        }
        return self::$_customer;
    }

    public static function getFieldsList($categoryId = null)
    {
        $models = [
            'title' => new Field(['attributes' => ['id' => 'title', 'title' => Yii::t('app', 'Title'), 'type' => FieldList::TYPE_STRING, 'filter' => FieldList::FILTER_STRING]]),
            'price' => new Field(['attributes' => ['id' => 'price', 'title' => Yii::t('app', 'Price'), 'type' => FieldList::TYPE_NUMBER, 'filter' => FieldList::FILTER_NUMBER, 'unit' => 'ریال']]),
            'exist' => new Field(['attributes' => ['id' => 'exist', 'title' => Yii::t('app', 'Exist'), 'type' => FieldList::TYPE_BOOLEAN, 'filter' => FieldList::FILTER_2STATE,]]),
        ];

        if ($categoryId) {
            $models = $models + Field::find()->where(['category_id' => $categoryId])->orderBy(new Expression('-`seq` DESC'))->indexBy('id')->all();
        }

        return ArrayHelper::toArray($models);
    }

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\QueryParamAuth',
                'user' => Yii::$app->customerApi,
                'optional' => ['*'],
                'tokenParam' => '_token',
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => Yii::$app->customerApi,
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                },
                'rules' => [
                    [
                        'actions' => ['constant', 'search', 'product', 'info',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['profile', 'signout', 'basket', 'basket-add', 'basket-remove', 'invoice', 'invoice-add', 'invoice-view', 'invoice-remove',],
                        'allow' => true,
                        'verbs' => ['POST'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public static function actionConstant()
    {
        return [
            'type' => FieldList::typeList(),
            'filter' => FieldList::filterList(),
            'opertaion' => FieldList::opertaionList(),
            'color' => Color::getList(),
            'province' => Province::getList(),
            'invoiceStatuses' => Invoice::statuses(),
        ];
    }

    public function actionSearch($categoryId = null)
    {
        $searchParams = Yii::$app->request->post('Search', []);
        $page = Yii::$app->request->post('page');
        $sort = Yii::$app->request->post('sort');
        //
        $blog = Yii::$app->blog->getIdentity();
        //
        $sortAttributes = [
            '-created_at' => Yii::t('app', 'Newest'),
            'created_at' => Yii::t('app', 'Oldest'),
            '-title' => Yii::t('app', 'Title (Desc)'),
            'title' => Yii::t('app', 'Title (Asc)'),
        ];
        ////
        $query = Product::find()->where(['AND', ['blog_name' => $blog['name'], 'status' => Status::STATUS_ACTIVE,]]);
        //
        $categories = self::categories();
        if ($categoryId && isset($categories[$categoryId])) {
            $query->andWhere(['category_id' => $categoryId,]);
        } else {
            $query->andWhere(['category_id' => array_keys($categories),]);
            $categoryId = null;
        }
        //
        $fields = self::getFieldsList($categoryId);
        //
        $search = [];
        foreach ($fields as $fieldId => $field) {
            $search[$fieldId] = [];
            if (!isset($searchParams[$fieldId]) || !is_array($searchParams[$fieldId])) {
                continue;
            }
            foreach ($searchParams[$fieldId] as $filter) {
                $model = DynamicModel::validateData(['field' => $fieldId, 'type' => $field['type'], 'category_id' => $field['category_id'], 'operation' => null, 'value' => null], [
                            [['!field', '!type', 'operation', 'value',], 'required'],
                            [['operation'], 'in', 'range' => array_keys(FieldList::getFilterOpertaion($field['filter']))],
                ]);
                $model->load($filter, '');
                if ($model->validate()) {
                    if ($model->operation == FieldList::FILTER_MULTI) {
                        $model->value = Helper::normalizeArray($model->value, true);
                    }
                    $search[$fieldId][] = $model->toArray();
                }
            }
        }
        //

        $fieldStringHasFilter = false;
        $fieldStringQuery = FieldString::find()->select('product_id');

        $fieldNumberHasFilter = false;
        $FieldNumberQuery = FieldNumber::find()->select('product_id');

        foreach ($search as $field) {
            foreach ($field as $filter) {
                if ($filter['category_id']) {
                    if ($filter['type'] == FieldList::TYPE_STRING) {
                        $fieldStringHasFilter = true;
                        $fieldStringQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['value']], ['=', 'field_id', $filter['field']]]);
                    } elseif ($filter['type'] == FieldList::TYPE_NUMBER || $filter['type'] == FieldList::TYPE_BOOLEAN) {
                        $fieldNumberHasFilter = true;
                        $FieldNumberQuery->andFilterWhere(['AND', [$filter['operation'], 'value', $filter['value']], ['=', 'field_id', $filter['field']]]);
                    }
                } elseif ($filter['field'] == 'title') {
                    $query->andFilterWhere([$filter['operation'], $filter['field'], $filter['value']]);
                } elseif ($filter['field'] == 'price') {
                    if ($filter['operation'] == '<') {
                        $query->andFilterWhere([$filter['operation'], 'price_min', $filter['value']]);
                    } elseif ($filter['operation'] == '>') {
                        $query->andFilterWhere([$filter['operation'], 'price_max', $filter['value']]);
                    } elseif ($filter['operation'] == '=') {
                        $query->andFilterWhere(['OR', [$filter['operation'], 'price_min', $filter['value']], [$filter['operation'], 'price_min', $filter['value']]]);
                    } elseif ($filter['operation'] == '<>') {
                        $query->andFilterWhere(['AND', [$filter['operation'], 'price_min', $filter['value']], [$filter['operation'], 'price_min', $filter['value']]]);
                    } elseif ($filter['operation'] == 'IN') {
                        $query->andFilterWhere(['OR', [$filter['operation'], 'price_min', $filter['value']], [$filter['operation'], 'price_min', $filter['value']]]);
                    } elseif ($filter['operation'] == 'NOT IN') {
                        $query->andFilterWhere(['AND', [$filter['operation'], 'price_min', $filter['value']], [$filter['operation'], 'price_min', $filter['value']]]);
                    }
                } elseif ($filter['field'] == 'exist') {
                    if (($filter['operation'] == '=') == boolval($filter['value'])) {
                        $query->andWhere(['not', ['price_min' => null]]);
                        $query->andWhere(['not', ['price_max' => null]]);
                    } else {
                        $query->andWhere(['price_min' => null]);
                        $query->andWhere(['price_max' => null]);
                    }
                }
            }
        }

        if ($fieldStringHasFilter) {
            $query->andWhere(['id' => $fieldStringQuery]);
        }

        if ($fieldNumberHasFilter) {
            $query->andWhere(['id' => $FieldNumberQuery]);
        }

        $products = [];
        $productsFields = [];
        $countOfResults = $query->count('id');

        $singleSort = new SingleSort([
            'sort' => $sort,
            'sortAttributes' => $sortAttributes,
        ]);

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => 12,
            ],
            'totalCount' => $countOfResults,
        ]);

        if ($countOfResults > 0) {
            $products = $query->orderBy([$singleSort->attribute => $singleSort->order])->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        }

        if ($countOfResults > 0 && $categoryId) {
            $productsId = ArrayHelper::getColumn($products, 'id');
            if ($productsId) {
                $productFieldResults = array_merge(
                        FieldString::find()->where(['product_id' => $productsId])->all(),
                        FieldNumber::find()->where(['product_id' => $productsId])->all()
                );
                foreach ($productFieldResults as $productFieldResultKey => $productFieldResult) {
                    if (isset($productFields[$productFieldResult->field_id]['values']) == false) {
                        $productsFields[$productFieldResult->product_id][$productFieldResult->field_id] = ['values' => []] + $fields[$productFieldResult->field_id];
                    }
                    $productsFields[$productFieldResult->product_id][$productFieldResult->field_id]['values'][] = $productFieldResult->value;
                }
                foreach ($productsFields as $productFieldsKey => $productFields) {
                    usort($productsFields[$productFieldsKey], function ($a, $b) {
                        if ($a['seq'] === $b['seq']) {
                            return 1;
                        }
                        if ($b['seq'] === null) {
                            return 1;
                        }
                        if ($a['seq'] === null) {
                            return -1;
                        }
                        return ($a['seq'] > $b['seq']) ? 1 : -1;
                    });
                }
            }
        }

        return [
            '_categories' => $categories,
            'categoryId' => $categoryId,
            'products' => $products,
            'productsFields' => $productsFields,
            'search' => $search,
            'fields' => $fields,
            'sort' => [
                'attribute' => $singleSort->sort,
                'attributes' => $singleSort->sortAttributes,
            ],
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
        ];
    }

    public function actionProduct($id)
    {
        $blog = self::blog();
        $categories = self::categories();

        $product = Product::find()->where(['AND', ['id' => $id, 'blog_name' => $blog['name'], 'status' => Status::STATUS_ACTIVE, 'category_id' => array_keys($categories)]])->one();
        if ($product) {
            $product = ArrayHelper::toArray($product);
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $fields = self::getFieldsList($product['category_id']);

        $productFields = [];
        $productFieldResults = array_merge(
                FieldString::find()->where(['product_id' => $product['id']])->all(),
                FieldNumber::find()->where(['product_id' => $product['id']])->all()
        );
        foreach ($productFieldResults as $productFieldResult) {
            if (isset($productFields[$productFieldResult->field_id]['values']) == false) {
                $productFields[$productFieldResult->field_id] = ['values' => []] + $fields[$productFieldResult->field_id];
            }
            $productFields[$productFieldResult->field_id]['values'][] = $productFieldResult['value'];
        }
        usort($productFields, function ($a, $b) {
            if ($a['seq'] === $b['seq']) {
                return 1;
            }
            if ($b['seq'] === null) {
                return 1;
            }
            if ($a['seq'] === null) {
                return -1;
            }
            return ($a['seq'] > $b['seq']) ? 1 : -1;
        });

        $images = Gallery::find()->where(['type' => Gallery::TYPE_PRODUCT, 'status' => Status::STATUS_ACTIVE, 'product_id' => $product['id']])->indexBy('name')->all();

        $packages = Package::find()->where(['status' => Status::STATUS_ACTIVE, 'product_id' => $product['id']])->all();

        return [
            '_categories' => $categories,
            'categoryId' => $product['category_id'],
            'product' => $product,
            'productFields' => $productFields,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images, ['name', 'updated_at', 'width', 'height']),
            'packages' => ArrayHelper::toArray($packages, ['price', 'guaranty', 'des']),
        ];
    }

    public function actionSignin()
    {
        $signin = Customer::signin(Yii::$app->request->post());
        if ($signin == null) {
            throw new BadRequestHttpException();
        }
        if ($user = $signin->getCustomer()) {
            return $user->response(true);
        }
        return $signin->response();
    }

    public function actionSignout()
    {
        $signout = Yii::$app->customerApi->getIdentity();
        if (!$signout) {
            throw new NotFoundHttpException();
        }
        $signout = $signout->signout();
        if ($signout == null) {
            throw new BadRequestHttpException();
        }
        return $signout->response();
    }

    public function actionSignup()
    {
        $signup = Customer::signup(Yii::$app->request->post());
        if ($signup == null) {
            throw new BadRequestHttpException();
        }
        if ($signup->hasErrors()) {
            return $signup->response();
        }
        return $signup->response(true);
    }

    public function actionResetPasswordRequest()
    {
        $resetPasswordRequest = Customer::resetPasswordRequest(Yii::$app->request->post());
        if ($resetPasswordRequest == null) {
            throw new BadRequestHttpException();
        }
        return $resetPasswordRequest->response();
    }

    public function actionResetPassword()
    {
        $resetPassword = Customer::resetPassword(Yii::$app->request->post());
        if ($resetPassword == null) {
            throw new BadRequestHttpException();
        }
        return $resetPassword->response();
    }

    /*
      public function actionProfile()
      {
      $profile = Yii::$app->customerApi->getIdentity();
      if (!$profile) {
      throw new NotFoundHttpException();
      }
      $profile = $profile->profile(Yii::$app->request->post());
      if ($profile == null) {
      throw new BadRequestHttpException();
      }
      return $profile->response();
      }
     * 
     */

    public function actionInfo()
    {
        return [];
    }

    public static function actionBasket()
    {
        $customer = self::customer();
        $categories = self::categories();
        //
        $packages = [];
        $products = [];

        $baskets = Basket::find()
                        ->where(['invoice_id' => null])
                        ->andWhere(['customer_id' => $customer['id']])
                        ->andWhere(['package_id' => Package::getActivePackagesQueryByCategories($categories)->select('id')])
                        ->asArray()->indexBy('id')->all();

        if (!empty($baskets)) {
            $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
            $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();
        }

        return [
            '_categories' => $categories,
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
        ];
    }

    public static function actionBasketAdd($package_id)
    {
        $blog = self::blog();
        $customer = self::customer();
        $categories = self::categories();
        $cnt = Yii::$app->request->post('cnt');
        //
        Basket::$activeCategories = $categories;

        $basket = Basket::findDuplicate($customer['id'], $package_id);
        if ($basket == null) {
            $basket = new Basket();
            $basket->cnt = 0;
        }
        $basket->cnt = (empty($cnt) ? $basket->cnt + 1 : $cnt);
        $basket->blog_name = $blog['name'];
        $basket->customer_id = $customer['id'];
        $basket->package_id = $package_id;
        $basket->invoice_id = null;
        $basket->save();

        return [
            '_categories' => $categories,
            'package' => ($basket->package ? $basket->package->toArray() : null),
            'basket' => $basket->attributes,
            'errors' => $basket->errors,
        ];
    }

    public static function actionBasketRemove($package_id)
    {
        $customer = self::customer();
        //
        $status = false;
        $basket = Basket::findDuplicate($customer['id'], $package_id);
        if ($basket) {
            $status = $basket->delete();
        }

        return [
            'status' => $status,
        ];
    }

    public static function actionInvoiceAdd()
    {
        $blog = self::blog();
        $customer = self::customer();
        $categories = self::categories();
        $params = Yii::$app->request->post();
        //
        $invoice = new Invoice();
        $packages = [];
        $products = [];

        $baskets = Basket::find()
                        ->where(['invoice_id' => null])
                        ->andWhere(['customer_id' => $customer['id']])
                        ->andWhere(['package_id' => Package::getActivePackagesQueryByCategories($categories)->select('id')])
                        ->asArray()->indexBy('id')->all();
        if ($baskets != []) {
            $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
            $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();
            $invoice->load($params);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $invoice->setPriceByArrayOfBasketsAndPackages($baskets, $packages);
                $invoice->status = Status::STATUS_UNVERIFIED;
                $invoice->blog_name = $blog['name'];
                $invoice->customer_id = $customer['id'];
                $invoice->save();
                Basket::updateAll(['invoice_id' => $invoice->id], ['id' => array_keys($baskets)]);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }

        return [
            '_categories' => $categories,
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
            'invoice' => $invoice->attributes,
            'errors' => $invoice->errors,
        ];
    }

    public function actionInvoice()
    {
        $blog = self::blog();
        $customer = self::customer();
        $categories = self::categories();
        //
        $page = Yii::$app->request->post('page');

        $query = Invoice::find()->where(['blog_name' => $blog['name']])->andWhere(['customer_id' => $customer['id']]);
        $countOfResults = $query->count('id');

        $pagination = new Pagination([
            'params' => [
                'page' => $page,
                'per-page' => 15,
            ],
            'totalCount' => $countOfResults,
        ]);

        $invoices = [];
        if ($countOfResults > 0) {
            $invoices = $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->indexBy('id')->asArray()->all();
        }

        return [
            '_categories' => $categories,
            'invoices' => $invoices,
            'pagination' => [
                'page_count' => $pagination->getPageCount(),
                'page_size' => $pagination->getPageSize(),
                'page' => $pagination->getPage(),
                'total_count' => $countOfResults,
            ],
        ];
    }

    public static function actionInvoiceRemove($id)
    {
        $blog = self::blog();
        $customer = self::customer();
        $categories = self::categories();
        //
        $invoice = Invoice::find()->where(['id' => $id])->andWhere(['blog_name' => $blog['name']])->andWhere(['customer_id' => $customer['id']])->andWhere(['status' => [Invoice::STATUS_VERIFIED, Invoice::STATUS_UNVERIFIED]])->one();
        if (empty($invoice)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        if ($invoice->status == Invoice::STATUS_UNVERIFIED) {
            $invoice->status = Invoice::STATUS_CUSTOMER_DELETED_UNVERIFIED;
        } else {
            $invoice->status = Invoice::STATUS_CUSTOMER_DELETED_VERIFIED;
        }
        return [
            'status' => $invoice->save(false),
        ];
    }

    public function actionInvoiceView($id)
    {
        $blog = self::blog();
        $customer = self::customer();
        $categories = self::categories();
        //

        $invoice = Invoice::find()->where(['id' => $id])->andWhere(['blog_name' => $blog['name']])->andWhere(['customer_id' => $customer['id']])->asArray()->one();
        if (empty($invoice)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $baskets = Basket::find()->where(['invoice_id' => $invoice['id']])->asArray()->all();
        $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
        $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();

        return [
            '_categories' => $categories,
            'invoice' => $invoice,
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
        ];
    }

}
