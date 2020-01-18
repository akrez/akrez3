<?php

namespace app\modules\api\controllers;

use app\components\SingleSort;
use app\controllers\Controller as BaseController;
use app\models\Category;
use app\models\Field;
use app\models\FieldList;
use app\models\FieldNumber;
use app\models\FieldString;
use app\models\Gallery;
use app\models\Package;
use app\models\Product;
use app\models\Status;
use Yii;
use yii\base\DynamicModel;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class V1Controller extends BaseController
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

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\HttpBearerAuth',
                'user' => Yii::$app->customerApi,
                'optional' => ['*'],
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => Yii::$app->customerApi,
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                },
                'rules' => [
                    [
                        'actions' => ['search', 'product',],
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
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

    public function actionSearch()
    {
        ////
        $searchParams = Yii::$app->request->get('Search', []);
        $page = Yii::$app->request->get('page');
        $sort = Yii::$app->request->get('sort');
        $categoryId = Yii::$app->request->get('categoryId');
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
            '_blog' => self::blog(),
            '_customer' => self::customer(),
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
            'opertaions' => FieldList::opertaionList(),
        ];
    }

    public static function actionProduct($id)
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
            '_blog' => self::blog(),
            '_customer' => self::customer(),
            '_categories' => $categories,
            'categoryId' => $product['category_id'],
            'product' => $product,
            'productFields' => $productFields,
            'fields' => $fields,
            'images' => ArrayHelper::toArray($images, ['name', 'updated_at', 'width', 'height']),
            'packages' => ArrayHelper::toArray($packages, ['price', 'guaranty', 'des']),
        ];
    }

}
