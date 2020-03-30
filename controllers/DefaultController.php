<?php

namespace app\controllers;

use app\components\Jdf;
use app\models\CategorySearch;
use app\models\LogApi;
use app\models\ProductSearch;
use Yii;
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
        //
        $dbChartData = LogApi::statSummary($blog->name, '1398-12-01');
        $dbChartData = ArrayHelper::map($dbChartData, 'created_date', 'cnt', 'have_action_primary');
        //
        $chartData = [
            'labels' => [],
            'haveActionPrimaryCount' => [],
            'dontHaveActionPrimaryCount' => [],
            'sumCount' => [],
        ];
        for ($d = 0; $d <= 29; $d++) {
            $pastDaysTimeStamp = strtotime(($d - 29) . " days");
            $jdateFormatedDay = Jdf::jdate('Y-m-d', $pastDaysTimeStamp);
            $chartData['labels'][$d] = $jdateFormatedDay;
            $chartData['haveActionPrimaryCount'][$d] = (isset($dbChartData[1][$jdateFormatedDay]) ? $dbChartData[1][$jdateFormatedDay] : 0);
            $chartData['dontHaveActionPrimaryCount'][$d] = (isset($dbChartData[0][$jdateFormatedDay]) ? $dbChartData[0][$jdateFormatedDay] : 0);
            $chartData['sumCount'][] = $chartData['haveActionPrimaryCount'][$d] + $chartData['dontHaveActionPrimaryCount'][$d];
        }
        //
        return $this->render('index', ['chartData' => $chartData]);
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
