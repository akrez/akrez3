<?php

namespace app\models;

use app\components\Email;
use Exception;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%Customer}}".
 *
 * @property int $id
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $status
 * @property string $token
 * @property string $password_hash
 * @property string $reset_token
 * @property string $reset_at
 * @property string $email
 * @property string $mobile
 *
 */
class Customer extends ActiveRecord implements IdentityInterface
{

    const TIMEOUT_RESET = 120;

    public $password;
    public $_customer;

    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            //signup
            [0 => ['email',], 1 => 'required', 'on' => 'signup',],
            [0 => ['email',], 1 => 'unique', 'targetAttribute' => ['email', 'blog_name'], 'message' => Yii::t('yii', '{attribute} "{value}" has already been taken.'), 'on' => 'signup',],
            [0 => ['email',], 1 => 'email', 'on' => 'signup',],
            [0 => ['password',], 1 => 'required', 'on' => 'signup',],
            [0 => ['password',], 1 => 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signup',],
            //signin
            [0 => ['email',], 1 => 'required', 'on' => 'signin',],
            [0 => ['email',], 1 => 'email', 'on' => 'signin',],
            [0 => ['password',], 1 => 'required', 'on' => 'signin',],
            [0 => ['password',], 1 => 'passwordValidation', 'on' => 'signin',],
            [0 => ['password',], 1 => 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'signin',],
            //resetPasswordRequest
            [0 => ['email',], 1 => 'required', 'on' => 'resetPasswordRequest',],
            [0 => ['email',], 1 => 'findValidCustomerByEmailValidation', 'on' => 'resetPasswordRequest',],
            [0 => ['email',], 1 => 'email', 'on' => 'resetPasswordRequest',],
            //resetPassword
            [0 => ['email',], 1 => 'required', 'on' => 'resetPassword',],
            [0 => ['email',], 1 => 'findValidCustomerByEmailResetTokenValidation', 'on' => 'resetPassword',],
            [0 => ['email',], 1 => 'email', 'on' => 'resetPassword',],
            [0 => ['password',], 1 => 'required', 'on' => 'resetPassword',],
            [0 => ['password',], 1 => 'minLenValidation', 'params' => ['min' => 6,], 'on' => 'resetPassword',],
            [0 => ['reset_token',], 1 => 'required', 'on' => 'resetPassword',],
        ];
    }

    /////

    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->andWhere(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()->where(['token' => $token])->andWhere(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->one();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->token;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /////

    public function passwordValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmail($this->email);
            if ($customer && $customer->validatePassword($this->password)) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function findValidCustomerByEmailValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmail($this->email);
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function findValidCustomerByEmailResetTokenValidation($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $customer = Customer::findValidCustomerByEmailResetToken($this->email, $this->reset_token);
            if ($customer) {
                return $this->_customer = $customer;
            }
            $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        }
        return $this->_customer = null;
    }

    public function minLenValidation($attribute, $params, $validator)
    {
        $min = $params['min'];
        if (strlen($this->$attribute) < $min) {
            $this->addError($attribute, Yii::t('yii', '{attribute} must be no less than {min}.', ['min' => $min, 'attribute' => $this->getAttributeLabel($attribute)]));
        }
    }

    public function maxLenValidation($attribute, $params, $validator)
    {
        $max = $params['max'];
        if ($max < strlen($this->$attribute)) {
            $this->addError($attribute, Yii::t('yii', '{attribute} must be no greater than {max}.', ['max' => $max, 'attribute' => $this->getAttributeLabel($attribute)]));
        }
    }

    public function setPasswordHash($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function setAuthKey()
    {
        return $this->token = preg_replace("/[^a-z0-9A-Z]+/i", "", Yii::$app->security->generateRandomString());
    }

    public function setResetToken()
    {
        if (empty($this->reset_token) || time() - self::TIMEOUT_RESET > $this->reset_at) {
            $this->reset_token = self::generateResetToken();
        }
        $this->reset_at = time();
    }

    public static function findValidCustomerByEmail($email)
    {
        return self::find()->where(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->andWhere(['blog_name' => Yii::$app->blog->name()])->andWhere(['email' => $email])->one();
    }

    public static function findValidCustomerByEmailResetToken($email, $resetToken)
    {
        return self::find()->where(['status' => [Status::STATUS_UNVERIFIED, Status::STATUS_ACTIVE, Status::STATUS_DISABLE]])->andWhere(['email' => $email])->andWhere(['reset_token' => $resetToken])->andWhere(['>', 'reset_at', time() - self::TIMEOUT_RESET])->one();
    }

    public function generateResetToken()
    {
        do {
            $rand = rand(100000, 999999);
            $model = self::find()->where(['reset_token' => $rand])->one();
        } while ($model != null);
        return $rand;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function info($includeToken = false)
    {
        return [
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'status' => $this->status,
            'blog_name' => $this->blog_name,
            'token' => ($includeToken ? $this->token : null),
        ];
    }

    public function response($includeToken = false)
    {
        return [
            'customer' => $this->info($includeToken),
            'errors' => $this->errors,
        ];
    }

    /////

    public static function signup($input)
    {
        try {
            $signup = new Customer(['scenario' => 'signup']);
            $signup->load($input, '');
            $signup->status = Status::STATUS_UNVERIFIED;
            $signup->blog_name = Yii::$app->blog->getIdentity()->name;
            $signup->setAuthKey();
            $signup->setPasswordHash($signup->password);
            $signup->save();
            return $signup;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function signin($input)
    {
        try {
            $signin = new Customer(['scenario' => 'signin']);
            $signin->load($input, '');
            $signin->validate();
            return $signin;
        } catch (Exception $e) {
            return null;
        }
    }

    public function signout()
    {
        try {
            $this->setAuthKey();
            $this->save(false);
            return $this;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function resetPasswordRequest($input)
    {
        try {
            $resetPasswordRequest = new Customer(['scenario' => 'resetPasswordRequest']);
            $resetPasswordRequest->load($input, '');
            if ($resetPasswordRequest->validate()) {
                $user = $resetPasswordRequest->getCustomer();
                $user->setResetToken();
                if ($user->save(false)) {
                    Email::customerResetPasswordRequest($user, Yii::$app->blog->getIdentity());
                } else {
                    return null;
                }
            }
            return $resetPasswordRequest;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function resetPassword($input)
    {
        try {
            $resetPassword = new Customer(['scenario' => 'resetPassword']);
            $resetPassword->load($input, '');
            if ($resetPassword->validate()) {
                $user = $resetPassword->getCustomer();
                $user->reset_token = null;
                $user->reset_at = null;
                $user->status = Status::STATUS_ACTIVE;
                $user->setPasswordHash($resetPassword->password);
                if ($user->save(false)) {
                    return $resetPassword;
                }
                return null;
            }
            return $resetPassword;
        } catch (Exception $e) {
            return null;
        }
    }

}
