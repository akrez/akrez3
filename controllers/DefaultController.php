<?php

namespace app\controllers;

use app\components\Jdf;
use app\models\CategorySearch;
use app\models\CustomerSearch;
use app\models\LogSearch;
use app\models\ProductSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
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
        $blog = Yii::$app->blog->getIdentity();
        /////
        $dbChartSummaryData = LogSearch::statSummary($blog->name, Jdf::jdate('Y-m-d', strtotime(-30 . " days")));
        $dbChartSummaryData = ArrayHelper::map($dbChartSummaryData, 'created_date', 'cnt', 'has_category');
        //
        $chartSummaryData = [
            'labels' => [],
            'haveActionPrimaryCount' => [],
            'dontHaveActionPrimaryCount' => [],
            'sumCount' => [],
        ];
        for ($d = 0; $d <= 29; $d++) {
            $pastDaysTimeStamp = strtotime(($d - 29) . " days");
            $jdateFormatedDay = Jdf::jdate('Y-m-d', $pastDaysTimeStamp);
            $chartSummaryData['labels'][$d] = $jdateFormatedDay;
            $chartSummaryData['dontHaveCategoryId'][$d] = (isset($dbChartSummaryData[0][$jdateFormatedDay]) ? $dbChartSummaryData[0][$jdateFormatedDay] : 0);
            $chartSummaryData['haveCategoryId'][$d] = (isset($dbChartSummaryData[1][$jdateFormatedDay]) ? $dbChartSummaryData[1][$jdateFormatedDay] : 0);
            $chartSummaryData['sum'][$d] = $chartSummaryData['haveCategoryId'][$d] + $chartSummaryData['dontHaveCategoryId'][$d];
        }
        $chartSummaryDataProvider = new ActiveDataProvider([
            'query' => LogSearch::statLastCountQuery($blog->name),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);
        /////
        return $this->render('index', [
                    'chartSummaryData' => $chartSummaryData,
                    'chartSummaryDataProvider' => $chartSummaryDataProvider,
                    'list' => [
                        'customers' => CustomerSearch::userValidQuery()->andWhere(['id' => ArrayHelper::getColumn($chartSummaryDataProvider->getModels(), 'user_id')])->indexBy('id')->all(),
                        'categories' => CategorySearch::userValidQuery()->andWhere(['id' => ArrayHelper::getColumn($chartSummaryDataProvider->getModels(), 'category_id')])->indexBy('id')->all(),
                    ]
        ]);
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
