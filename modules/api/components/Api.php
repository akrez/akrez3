<?php

namespace app\modules\api\components;

use common\components\Helper;
use common\components\SingleSort;
use common\models\Basket;
use common\models\Category;
use common\models\Field;
use common\models\FieldList;
use common\models\FieldNumber;
use common\models\FieldString;
use common\models\Gallery;
use common\models\Invoice;
use common\models\Package;
use common\models\Product;
use common\models\Status;
use Yii;
use yii\base\Component;
use yii\base\DynamicModel;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class Api extends Component
{

    private static $_dp = [];
    private static $_category = false;

    public static function categories()
    {
        if (self::$_category === false) {
            self::$_category = Category::find()->select(['title', 'id'])->where(['blog_name' => self::blogAttribute('name'), 'status' => Status::STATUS_ACTIVE])->indexBy('id')->asArray()->column();
        }
        return self::$_category;
    }

    public static function blog()
    {
        if (Yii::$app->blog->getIdentity()) {
            return Yii::$app->blog->getIdentity()->info();
        }
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }

    public static function blogAttribute($attribute)
    {
        $blog = self::blog();
        if ($blog && array_key_exists($attribute, $blog)) {
            return $blog[$attribute];
        }
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }

    public static function customer()
    {
        if (Yii::$app->user->getIdentity()) {
            return Yii::$app->user->getIdentity()->info();
        }
        return null;
    }

    //HELPER

    public static function getDp()
    {
        return self::$_dp;
    }

    public static function url($controller, $action = null, $config = [])
    {
        if (\Yii::$app->params['isParked']) {
            return Url::to([0 => $controller . '/' . $action] + $config);
        } else {
            return Url::to([0 => $controller . '/' . $action, '_blog' => Yii::$app->request->get('_blog')] + $config);
        }
    }

    public static function blogFirstPageUrl()
    {
        return self::url('site', 'index');
    }

    public static function galleryUrl($name, $type, $whq = '400__67')
    {
        return self::url('site', 'gallery', ['type' => $type, 'whq' => $whq, 'name' => $name]);
    }

    public static function blogLogo($whq = '400__67')
    {
        return self::galleryUrl(self::getBlogAttribute('logo'), 'logo', '400__67');
    }

    public static function getBlogName($safe = true)
    {
        return Yii::$app->blog::name();
    }

    public static function getDpAttribute($attribute, $restricted = false)
    {
        if (isset(self::$_dp[$attribute])) {
            return self::$_dp[$attribute];
        }
        if ($restricted) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
        return [];
    }

    public static function getBlogAttribute($attribute, $safe = true)
    {
        if ($safe) {
            return Html::encode(Yii::$app->blog->attribute($attribute));
        }
        return Yii::$app->blog->attribute($attribute);
    }

    // API

    public static function search($params, $categoryId = null)
    {
        $page = Yii::$app->request->get('page');
        $sort = Yii::$app->request->get('sort');

        $sortAttributes = [
            '-created_at' => \Yii::t('app', 'Newest'),
            'created_at' => \Yii::t('app', 'Oldest'),
            '-title' => \Yii::t('app', 'Title (Desc)'),
            'title' => \Yii::t('app', 'Title (Asc)'),
        ];

        $blogName = self::blogAttribute('name');

        $query = Product::find()->where(['AND', ['blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE,]]);

        $categories = self::categories();
        if ($categoryId && isset($categories[$categoryId])) {
            $query->andWhere(['category_id' => $categoryId,]);
        } else {
            $query->andWhere(['category_id' => array_keys($categories),]);
            $categoryId = null;
        }

        $fields = self::getFieldsList($categoryId);
        $search = self::buildSearchModels('Search', $params, $fields);

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

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
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
            'opertaions' => FieldList::opertaionList(),
        ];
    }

    public static function product($productId)
    {
        $blogName = self::blogAttribute('name');

        $categories = self::categories();

        $product = Product::find()->where(['AND', ['id' => $productId, 'blog_name' => $blogName, 'status' => Status::STATUS_ACTIVE, 'category_id' => array_keys($categories)]])->one();
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

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
            'categoryId' => $product['category_id'],
            'product' => $product,
            'productFields' => $productFields,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images, ['name', 'updated_at', 'width', 'height']),
            'packages' => ArrayHelper::toArray($packages, ['price', 'guaranty', 'des']),
        ];
    }

    public static function basketAdd($packageId, $cnt = null)
    {
        $blogName = self::blogAttribute('name');
        $customerId = Yii::$app->user->getIdentity()->getId();

        $categories = self::categories();

        Basket::$activeCategories = $categories;

        $basket = Basket::findDuplicate($customerId, $packageId);
        if ($basket == null) {
            $basket = new Basket();
            $basket->cnt = 0;
        }
        $basket->cnt = (empty($cnt) ? $basket->cnt + 1 : $cnt);
        $basket->blog_name = $blogName;
        $basket->customer_id = $customerId;
        $basket->package_id = $packageId;
        $basket->invoice_id = null;
        $basket->save();

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
            'package' => ($basket->package ? $basket->package->toArray() : null),
            'status' => ($basket->errors ? false : true),
            'basket' => $basket->attributes,
            'errors' => $basket->errors,
        ];
    }

    public static function basketRemove($packageId)
    {
        $customerId = Yii::$app->user->getIdentity()->getId();

        $status = false;

        $basket = Basket::findDuplicate($customerId, $packageId);
        if ($basket) {
            $status = $basket->delete();
        }

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'status' => $status,
        ];
    }

    public static function basket($params)
    {
        $blogName = self::blogAttribute('name');
        $customerId = Yii::$app->user->getIdentity()->getId();
        $categories = self::categories();
        $status = false;
        $invoice = new Invoice();
        $packages = [];
        $products = [];

        $baskets = Basket::find()
                        ->where(['invoice_id' => null])
                        ->andWhere(['customer_id' => $customerId])
                        ->andWhere(['package_id' => Package::getActivePackagesQueryByCategories($categories)->select('id')])
                        ->asArray()->indexBy('id')->all();

        if ($baskets != []) {
            $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
            $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();
            if ($invoice->load($params)) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $invoice->setPriceByArrayOfBasketsAndPackages($baskets, $packages);
                    $invoice->status = Status::STATUS_UNVERIFIED;
                    $invoice->blog_name = $blogName;
                    $invoice->customer_id = $customerId;
                    $invoice->save();
                    Basket::updateAll(['invoice_id' => $invoice->id], ['id' => array_keys($baskets)]);
                    $transaction->commit();
                    $status = true;
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
            'status' => $status,
            'invoice' => $invoice->attributes,
            'errors' => $invoice->errors,
        ];
    }

    public function invoice()
    {
        $blogName = self::blogAttribute('name');
        $customerId = Yii::$app->user->getIdentity()->getId();
        $categories = self::categories();

        $invoices = Invoice::find()->where(['blog_name' => $blogName])->andWhere(['customer_id' => $customerId])->orderBy('id DESC')->asArray()->indexBy('id')->all();

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
            'invoices' => $invoices,
        ];
    }

    public function invoiceView($id)
    {
        $blogName = self::blogAttribute('name');
        $customerId = Yii::$app->user->getIdentity()->getId();
        $categories = self::categories();

        $invoice = Invoice::find()->where(['id' => $id])->andWhere(['blog_name' => $blogName])->andWhere(['customer_id' => $customerId])->asArray()->one();
        if (empty($invoice)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $baskets = Basket::find()->where(['invoice_id' => $invoice['id']])->asArray()->all();
        $packages = Package::find()->where(['id' => ArrayHelper::getColumn($baskets, 'package_id')])->asArray()->indexBy('id')->all();
        $products = Product::find()->where(['id' => ArrayHelper::getColumn($packages, 'product_id')])->asArray()->indexBy('id')->all();

        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'categories' => $categories,
            'invoice' => $invoice,
            'baskets' => $baskets,
            'packages' => $packages,
            'products' => $products,
        ];
    }

    public function invoiceRemove($id)
    {
        $blogName = self::blogAttribute('name');
        $customerId = Yii::$app->user->getIdentity()->getId();

        $invoice = Invoice::find()->where(['id' => $id])->andWhere(['blog_name' => $blogName])->andWhere(['customer_id' => $customerId])->andWhere(['status' => [Invoice::STATUS_VERIFIED, Invoice::STATUS_UNVERIFIED]])->one();
        if (empty($invoice)) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        if ($invoice->status == Invoice::STATUS_UNVERIFIED) {
            $invoice->status = Invoice::STATUS_CUSTOMER_DELETED_UNVERIFIED;
        } else {
            $invoice->status = Invoice::STATUS_CUSTOMER_DELETED_VERIFIED;
        }
        return self::$_dp = [
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            'status' => $invoice->save(false),
        ];
    }

    private static function buildSearchModels($formName, $params, $fields)
    {
        $result = [];

        foreach ($fields as $fieldId => $field) {
            $result[$fieldId] = [];
            if (!isset($params[$formName][$fieldId]) || !is_array($params[$formName][$fieldId])) {
                continue;
            }

            foreach ($params[$formName][$fieldId] as $filter) {
                $model = DynamicModel::validateData(['field' => $fieldId, 'type' => $field['type'], 'category_id' => $field['category_id'], 'operation' => null, 'value' => null], [
                            [['!field', '!type', 'operation', 'value',], 'required'],
                            [['operation'], 'in', 'range' => array_keys(FieldList::getFilterOpertaion($field['filter']))],
                ]);

                $model->load($filter, '');
                if ($model->validate()) {
                    if ($model->operation == FieldList::FILTER_MULTI) {
                        $model->value = Helper::normalizeArray($model->value, true);
                    }
                    $result[$fieldId][] = $model->toArray();
                }
            }
        }

        return $result;
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

}
