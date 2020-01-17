<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\CategorySearch;
use app\models\FieldNumberSearch;
use app\models\FieldSearch;
use app\models\FieldStringSearch;
use app\models\Category;
use app\models\Field;
use Yii;
use app\components\WizardController;

class FieldController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Field(),
            'searchModel' => new FieldSearch(),
            'parentModel' => new Category(),
            'parentSearchModel' => new CategorySearch(),
        ]);
    }

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'delete'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return array_merge_recursive($behaviors, parent::behaviors());
    }

    public function actionIndex($parent_id, $id = null)
    {
        $this->wizard->findParentModel($parent_id);
        $isSuccessfull = null;
        if ($id) {
            $isSuccessfull = $this->wizard->update($id, Yii::$app->request->post(), [
                'category_id' => $parent_id,
            ]);
        } else {
            $isSuccessfull = $this->wizard->create(Yii::$app->request->post(), [
                'category_id' => $parent_id,
            ]);
        }
        if ($isSuccessfull) {
            return $this->refresh();
        }
        return $this->render('index', $this->wizard->index(Yii::$app->request->queryParams));
    }

    public function actionDelete($parent_id, $id)
    {
        $redirectUrl = AdminHelper::url(['field/index', 'parent_id' => $parent_id]);

        $fieldCount = FieldStringSearch::userValidQuery()->where(['field_id' => $id])->count('id') + FieldNumberSearch::userValidQuery()->where(['field_id' => $id])->count('id');
        if ($fieldCount) {
            $msg = Yii::t('app', 'alertRemoveDanger', ['count' => $fieldCount, 'child' => Yii::t('app', 'Product'), 'parent' => Yii::t('app', 'Field')]);
            Yii::$app->session->setFlash('danger', $msg);
            return $this->redirect($redirectUrl);
        }

        $this->wizard->delete($id);
        return $this->redirect($redirectUrl);
    }

}
