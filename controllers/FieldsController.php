<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\FieldNumberSearch;
use app\models\FieldSearch;
use app\models\FieldStringSearch;
use app\models\ProductSearch;
use app\models\FieldList;
use app\models\FieldNumber;
use app\models\FieldString;
use app\models\Product;
use Yii;
use yii\web\NotFoundHttpException;
use app\components\WizardController;
use yii\helpers\ArrayHelper;

class FieldsController extends Controller
{

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'parentModel' => new Product(),
            'parentSearchModel' => new ProductSearch(),
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

    public function actionIndex($parent_id)
    {
        $parentModel = $this->wizard->findParentModel($parent_id);
        $categoryFields = FieldSearch::userValidQuery()->andWhere(['category_id' => $parentModel->category_id])->indexBy('id')->all();
        $updatedAt = time();
        //
        $models = [];
        $insertDatas = [];
        $updateFieldsOptions = [];
        //
        foreach ($categoryFields as $fieldId => $categoryField) {
            if (in_array($categoryField->type, [FieldList::TYPE_STRING])) {
                $model = new FieldString();
            } elseif (in_array($categoryField->type, [FieldList::TYPE_NUMBER, FieldList::TYPE_BOOLEAN])) {
                $model = new FieldNumber();
            } else {
                break;
            }
            $model->updated_at = $updatedAt;
            $model->product_id = $parent_id;
            $model->field_id = $fieldId;
            //
            $model->value = (string) ArrayHelper::getValue(Yii::$app->request->post(), $model->formName() . ".$fieldId.value");
            if ($model->value != '' && $model->validate()) {
                $updateFieldsOptions[] = $model->field_id;
                $insertDatas[$model->tableName()][] = [$model->updated_at, $model->value, $model->product_id, $model->field_id];
                $model->value = '';
            }
            //
            $models[$fieldId] = $model;
        }
        //
        foreach ($insertDatas as $tableName => $insertData) {
            Yii::$app->db->createCommand()->batchInsert($tableName, ['updated_at', 'value', 'product_id', 'field_id'], $insertData)->execute();
        }
        //
        foreach ($categoryFields as $fieldId => $categoryField) {
            if (in_array($fieldId, $updateFieldsOptions)) {
                $this->updateFieldsOptions($categoryField);
            }
        }
        //
        return $this->render('index', [
                    'parentModel' => $parentModel,
                    'categoryFields' => $categoryFields,
                    'models' => [
                        'create' => $models,
                        'update' => array_merge($this->fetchUpdateModels(new FieldStringSearch), $this->fetchUpdateModels(new FieldNumberSearch)),
                    ],
        ]);
    }

    public function actionDelete($parent_id, $id, $form_name)
    {
        $stringModel = new FieldString();
        $numberModel = new FieldNumber();

        if ($stringModel->formName() == $form_name) {
            $this->wizard->searchModel = new FieldStringSearch();
        } elseif ($numberModel->formName() == $form_name) {
            $this->wizard->searchModel = new FieldNumberSearch();
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $this->wizard->delete($id);

        $categoryField = FieldSearch::userValidQuery()->andWhere(['id' => $this->wizard->model->field_id])->one();
        $this->updateFieldsOptions($categoryField);

        $redirectUrl = AdminHelper::url(['fields/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    private function fetchUpdateModels($instanceSearchModel)
    {
        return $instanceSearchModel->userValidQuery()->andWhere(['product_id' => $this->wizard->parentModel->id])->orderBy('field_id')->indexBy('id')->all();
    }

    private function updateFieldsOptions($categoryField)
    {
        $model = null;
        if (in_array($categoryField->type, [FieldList::TYPE_STRING])) {
            $model = new FieldStringSearch();
        } elseif (in_array($categoryField->type, [FieldList::TYPE_NUMBER, FieldList::TYPE_BOOLEAN])) {
            $model = new FieldNumberSearch();
        }
        //
        if ($model) {
            $categoryField->options = $model->userValidQuery()->select('value')->andWhere(['field_id' => $categoryField->id])->groupBy('value')->column();
            sort($categoryField->options);
            $categoryField->save();
        }
    }

}
