<?php

namespace app\controllers;

use app\models\Status;
use app\models\User;
use app\models\Gallery;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\models\Blog;
use yii\helpers\Url;
use app\components\Email;

class SiteController extends Controller
{

    public function behaviors()
    {
        $behaviors['access'] = [
            'rules' => [
                [
                    'actions' => ['error', 'index'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['?', '@'],
                ],
                [
                    'actions' => ['gallery'],
                    'allow' => true,
                    'verbs' => ['GET'],
                    'roles' => ['?', '@'],
                ],
                [
                    'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['blogs', 'create', 'signout'],
                    'allow' => true,
                    'verbs' => ['POST', 'GET'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return array_merge_recursive($behaviors, parent::behaviors());
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->layout = 'site';
            return true;
        }
        return false;
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => (Yii::$app->blog->hasIdentity() ? 'main' : 'site'),
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionBlogs()
    {
        $userId = Yii::$app->user->getId();
        $models = Blog::findBlogsForAdmin($userId);
        return $this->render('blogs', [
                    'models' => $models,
        ]);
    }

    public function actionCreate()
    {
        $model = new Blog();
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->getId();
            if ($model->save()) {
                $url = Url::to(['default/index', '_blog' => $model->name]);
                return $this->redirect($url);
            }
        }
        return $this->render('/default/form', [
                    'model' => $model,
        ]);
    }

    public function actionGallery($type, $whq, $name)
    {
        $path = Gallery::cache($type, $name, $whq);
        if ($path !== null) {
            return Yii::$app->response->sendFile($path, null, ['inline' => true]);
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public function actionSignin()
    {
        try {
            $signin = new User(['scenario' => 'signin']);
            if ($signin->load(Yii::$app->request->post()) && $signin->validate()) {
                Yii::$app->user->login($signin->getUser());
                return $this->goBack();
            }
            return $this->render('signin', ['model' => $signin]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionSignout()
    {
        try {
            $signout = Yii::$app->user->getIdentity();
            $signout->setAuthKey();
            if ($signout->save(false)) {
                Yii::$app->user->logout();
            }
            return $this->goHome();
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionSignup()
    {
        try {
            $signup = new User(['scenario' => 'signup']);
            if ($signup->load(\Yii::$app->request->post())) {
                $signup->status = Status::STATUS_UNVERIFIED;
                $signup->setAuthKey();
                $signup->setPasswordHash($signup->password);
                if ($signup->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertSignupSuccessfull'));
                    return $this->goBack();
                }
            }
            return $this->render('signup', ['model' => $signup]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPasswordRequest()
    {
        try {
            $resetPasswordRequest = new User(['scenario' => 'resetPasswordRequest']);
            if ($resetPasswordRequest->load(\Yii::$app->request->post()) && $resetPasswordRequest->validate()) {
                $user = $resetPasswordRequest->getUser();
                $user->setResetToken();
                if ($user->save(false) && Email::resetPasswordRequest($user)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertResetPasswordRequestSuccessfull'));
                    return $this->redirect(['site/index']);
                }
            }
            return $this->render('reset-password-request', ['model' => $resetPasswordRequest]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionResetPassword()
    {
        try {
            $resetPassword = new User(['scenario' => 'resetPassword']);
            if ($resetPassword->load(\Yii::$app->request->post()) && $resetPassword->validate()) {
                $user = $resetPassword->getUser();
                $user->reset_token = null;
                $user->reset_at = null;
                $user->status = Status::STATUS_ACTIVE;
                $user->setPasswordHash($resetPassword->password);
                if ($user->save(false)) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'alertResetPasswordSuccessfull'));
                    return $this->redirect(['site/index']);
                }
            }
            return $this->render('reset-password', ['model' => $resetPassword]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

}
