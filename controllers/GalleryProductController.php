<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\GallerySearch;
use app\models\ProductSearch;
use app\models\Gallery;
use app\models\Product;
use Yii;
use app\components\WizardController;
use yii\web\UploadedFile;

/**
 * GalleryController implements the CRUD actions for Gallery model.
 */
class GalleryProductController extends Controller
{

    public $type = null;

    public function init()
    {
        parent::init();
        $this->wizard = new WizardController([
            'newModel' => new Gallery(),
            'searchModel' => new GallerySearch(),
            'parentModel' => new Product(),
            'parentSearchModel' => new ProductSearch(),
        ]);
        $this->type = Gallery::TYPE_PRODUCT;
    }

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['index', 'delete', 'upload', 'default'],
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
        $this->wizard->findParentModel($parent_id);
        return $this->render('index', $this->wizard->index([]));
    }

    public function actionUpload($parent_id)
    {
        $this->wizard->findParentModel($parent_id);

        $gallery = new Gallery();
        $srcFile = UploadedFile::getInstance($gallery, 'image')->tempName;
        $errors = $gallery->upload($this->type, $srcFile, ['product_id' => $parent_id]);
        if ($errors) {
            Yii::$app->session->setFlash('danger', reset($errors));
        } else {
            if (empty($this->wizard->parentModel->image)) {
                $this->safeDefault($gallery->name, $this->wizard->parentModel);
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'alertGalleryUploadSuccessfull'));
        }

        $redirectUrl = AdminHelper::url([0 => 'gallery-product/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionDelete($name, $parent_id)
    {
        $this->wizard->delete($name);
        $redirectUrl = AdminHelper::url([0 => 'gallery-product/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    public function actionDefault($name, $parent_id)
    {
        $this->wizard->findModel($name);
        $this->wizard->findParentModel($parent_id);

        $this->safeDefault($name, $this->wizard->parentModel);

        $redirectUrl = AdminHelper::url([0 => 'gallery-product/index', 'parent_id' => $parent_id]);
        return $this->redirect($redirectUrl);
    }

    private function safeDefault($name, $parentModel)
    {
        $parentModel->image = $name;
        return $parentModel->save();
    }

}
