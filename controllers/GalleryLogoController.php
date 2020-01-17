<?php

namespace app\controllers;

use app\components\AdminHelper;
use app\models\GallerySearch;
use app\models\ProductSearch;
use app\models\Gallery;
use Yii;
use app\components\WizardController;
use yii\web\UploadedFile;

/**
 * GalleryController implements the CRUD actions for Gallery model.
 */
class GalleryLogoController extends Controller
{

    public $type = null;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->wizard = new WizardController([
            'newModel' => new Gallery(),
            'searchModel' => new GallerySearch(),
            'parentModel' => Yii::$app->blog->getIdentity(),
            'parentSearchModel' => null,
        ]);
        $this->type = Gallery::TYPE_LOGO;
        return true;
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

    public function actionIndex()
    {
        return $this->render('index', $this->wizard->index([]));
    }

    public function actionUpload()
    {

        $gallery = new Gallery();
        $srcFile = UploadedFile::getInstance($gallery, 'image')->tempName;
        $errors = $gallery->upload($this->type, $srcFile, []);
        if ($errors) {
            Yii::$app->session->setFlash('danger', reset($errors));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'alertGalleryUploadSuccessfull'));
        }

        $redirectUrl = AdminHelper::url(['gallery-logo/index']);
        return $this->redirect($redirectUrl);
    }

    public function actionDelete($name)
    {
        $this->wizard->delete($name);
        $redirectUrl = AdminHelper::url(['gallery-logo/index']);
        return $this->redirect($redirectUrl);
    }

    public function actionDefault($name)
    {
        $this->wizard->findModel($name);

        $this->wizard->parentModel->logo = $name;
        $this->wizard->parentModel->save();

        $redirectUrl = AdminHelper::url(['gallery-logo/index']);
        return $this->redirect($redirectUrl);
    }

}
