<?php
namespace app\controllers;

use Yii;
use app\models\CategorySearch;
use app\models\Category;
use app\components\WizardController;

class CategoryGarantiesController extends Controller
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
                    'actions' => ['index'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return array_merge_recursive($behaviors, parent::behaviors());
    }

    public function actionIndex($id)
    {
        $isSuccessfull = $this->wizard->update($id, Yii::$app->request->post(), [
            'blog_name' => Yii::$app->blog->name(),
        ]);
        if ($isSuccessfull) {
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams));
    }

}
