<?php

namespace app\modules\api\controllers;

use admin\modules\v1\components\Api;
use common\models\Customer;
use common\models\Gallery;
use common\models\Status;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller as BaseController;

class V1Controller extends BaseController
{

    const tokenParam = 'token';

    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => 'yii\filters\auth\HttpBearerAuth',
                'user' => Yii::$app->customerApi,
                //'tokenParam' => self::tokenParam,
                //'optional' => ['*'],
            ],
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'user' => Yii::$app->customerApi,
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                },
                'rules' => [
                    [
                        'actions' => ['signin', 'signup', 'reset-password-request', 'reset-password'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['signout'],
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'category', 'product'],
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['gallery', 'error'],
                        'allow' => true,
                        'verbs' => ['GET', 'POST'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['basket-add', 'basket-remove', 'basket', 'invoice', 'invoice-view', 'invoice-remove'],
                        'allow' => true,
                        'verbs' => ['GET'],
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        $get = (array) Yii::$app->request->get();
        Api::search($get);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->render('index');
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
            $signin = new Customer(['scenario' => 'signin']);
            if ($signin->load(Yii::$app->request->post()) && $signin->validate()) {
                Yii::$app->user->login($signin->getCustomer());
                $url = Yii::$app->user->getReturnUrl(Api::url('site', 'index'));
                return $this->redirect($url);
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
            return $this->redirect(Api::url('site', 'index'));
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionSignup()
    {
        try {
            $signup = new Customer(['scenario' => 'signup']);
            if ($signup->load(Yii::$app->request->post())) {
                $signup->status = Status::STATUS_UNVERIFIED;
                $signup->setAuthKey();
                $signup->setPasswordHash($signup->password);
                if ($signup->save()) {
                    Yii::$app->user->login($signup);
                    return $this->redirect(Api::url('site', 'index'));
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
            $resetPasswordRequest = new Customer(['scenario' => 'resetPasswordRequest']);
            if ($resetPasswordRequest->load(Yii::$app->request->post()) && $resetPasswordRequest->validate()) {
                $user = $resetPasswordRequest->getCustomer();
                $user->setResetToken();
                if ($user->save(false)) {
                    //Email::resetPasswordRequest($user);
                    return $this->redirect(Api::url('site', 'reset-password'));
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
            $resetPassword = new Customer(['scenario' => 'resetPassword']);
            if ($resetPassword->load(Yii::$app->request->post()) && $resetPassword->validate()) {
                $user = $resetPassword->getCustomer();
                $user->reset_token = null;
                $user->reset_at = null;
                $user->status = Status::STATUS_ACTIVE;
                $user->setPasswordHash($resetPassword->password);
                if ($user->save(false)) {
                    return $this->redirect(Api::url('site', 'signin'));
                }
            }
            return $this->render('reset-password', ['model' => $resetPassword]);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    public function actionCategory($id)
    {
        $get = (array) Yii::$app->request->get();
        Api::search($get, $id);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->render('category');
    }

    public function actionProduct($id)
    {
        Api::product($id);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->render('product');
    }

    public function actionBasketAdd($id, $cnt = null)
    {
        Api::basketAdd($id, $cnt);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        $result = Api::getDp();
        if ($result['status']) {
            return $this->redirect(Api::url('site', 'basket'));
        }
        if (isset($result['package']['product_id'])) {
            return $this->redirect(Api::url('site', 'product', ['id' => $result['package']['product_id']]));
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public function actionBasketRemove($id)
    {
        Api::basketRemove($id);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        $result = Api::getDp();
        if ($result['status']) {
            return $this->redirect(Api::url('site', 'basket'));
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    public function actionBasket()
    {
        $get = (array) Yii::$app->request->get();
        Api::basket($get);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        $result = Api::getDp();
        if ($result['status']) {
            return $this->redirect(Api::url('site', 'invoice'));
        }
        return $this->render('basket');
    }

    public function actionInvoice()
    {
        Api::invoice();
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->render('invoice');
    }

    public function actionInvoiceView($id)
    {
        Api::invoiceView($id);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->render('invoice_view');
    }

    public function actionInvoiceRemove($id)
    {
        Api::invoiceRemove($id);
        if ($this->isJson()) {
            return $this->asJson(Api::getDp());
        }
        return $this->redirect(Api::url('site', 'invoice'));
    }

}
