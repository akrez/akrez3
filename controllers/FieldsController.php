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
                    'actions' => ['index', 'create', 'delete'],
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
        $parentModel = $this->findParentModel($parent_id);

        $categoryFields = FieldSearch::userValidQuery()->andWhere(['category_id' => $parentModel->category_id])->indexBy('id')->all();

        $updatedAt = time();

        $newModels = [];
        foreach ($categoryFields as $fieldId => $categoryField) {
            if ($categoryField->type == FieldList::TYPE_STRING) {
                $model = new FieldString();
            } elseif (in_array($categoryField->type, [FieldList::TYPE_NUMBER, FieldList::TYPE_BOOLEAN])) {
                $model = new FieldNumber();
            } else {
                break;
            }
            $model->field = $categoryField;
            $model->updated_at = $updatedAt;
            $model->product_id = $parent_id;
            $model->field_id = $fieldId;
            $newModels[$fieldId] = $model;
        }

        if (Yii::$app->request->post('create')) {
            $this->create($categoryFields, new FieldString(), $newModels);
            $this->create($categoryFields, new FieldNumber(), $newModels);
            $url = AdminHelper::url(['fields/index', 'parent_id' => $parentModel->id]);
            return $this->redirect($url);
        }

        $dbStringModels = $this->update($categoryFields, new FieldString(), new FieldStringSearch());
        $dbNumberModels = $this->update($categoryFields, new FieldNumber(), new FieldNumberSearch());

        return $this->render('index', [
                    'models' => [
                        'update' => array_merge($dbStringModels, $dbNumberModels),
                        'create' => $newModels,
                    ],
                    'parentModel' => $parentModel,
                    'categoryFields' => $categoryFields,
        ]);
    }

    public function update($categoryFields, $instanceModel, $instanceSearchModel)
    {
        $models = $instanceSearchModel->userValidQuery()->andWhere(['product_id' => $this->wizard->parentModel->id])->orderBy('field_id')->indexBy('id')->all();
        foreach ($models as $id => $model) {
            $model->field = isset($categoryFields[$model->field_id]) ? $categoryFields[$model->field_id] : null;
        }

        $postedDatas = (array) Yii::$app->request->post($instanceModel->formName(), []);
        if (Yii::$app->request->post('update') && $postedDatas) {
            $deleteDatas = [];
            $insertDatas = [];

            $tableName = null;

            foreach ($postedDatas as $id => $postedData) {
                if (isset($models[$id])) {
                    $model = $models[$id];
                } else {
                    continue;
                }

                if ($model->load($postedData, '')) {
                    
                } else {
                    continue;
                }

                if ($model->validate()) {
                    $tableName = $model->tableName();
                    $deleteDatas[] = ['=', 'id', $id];
                    $insertDatas[] = [$model->id, $model->updated_at, $model->value, $model->product_id, $model->field_id];
                } else {
                    continue;
                }
            }

            if ($tableName) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    Yii::$app->db->createCommand()->delete($tableName, array_merge(['OR'], $deleteDatas))->execute();
                    Yii::$app->db->createCommand()->batchInsert($tableName, ['id', 'updated_at', 'value', 'product_id', 'field_id'], $insertDatas)->execute();
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }
        return $models;
    }

    public function create($categoryFields, $instanceModel, $models)
    {
        $postedDatas = (array) Yii::$app->request->post($instanceModel->formName(), []);
        if ($postedDatas) {
            $insertDatas = [];

            $tableName = null;

            foreach ($postedDatas as $id => $postedData) {
                if (isset($models[$id]) && $models[$id]->formName() == $instanceModel->formName()) {
                    $model = $models[$id];
                } else {
                    continue;
                }

                if ($model->load($postedData, '')) {
                    
                } else {
                    continue;
                }

                if ($model->validate()) {
                    $tableName = $model->tableName();
                    $insertDatas[] = [$model->updated_at, $model->value, $model->product_id, $model->field_id];
                } else {
                    continue;
                }
            }

            if ($tableName) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    Yii::$app->db->createCommand()->batchInsert($tableName, ['updated_at', 'value', 'product_id', 'field_id'], $insertDatas)->execute();
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }
        return $models;
    }

    public function actionDelete($parent_id, $id, $form_name)
    {
        $stringModel = new FieldString();
        $numberModel = new FieldNumber();

        if ($stringModel->formName() == $form_name) {
            $this->searchModel = new FieldStringSearch();
        } elseif ($numberModel->formName() == $form_name) {
            $this->searchModel = new FieldNumberSearch();
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $this->findModel($id)->delete();

        $redirectUrl = AdminHelper::url(['fields/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function findModel($id)
    {
        $model = $this->searchModel->userValidQuery($id)->one();
        if ($model) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public function findParentModel($id)
    {
        $this->wizard->parentModel = $this->wizard->parentSearchModel->userValidQuery($id)->one();
        if ($this->wizard->parentModel) {
            return $this->wizard->parentModel;
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

}
