<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $status
 * @property string $name
 * @property string $province
 * @property string $address
 * @property string $mobile
 * @property string $phone
 * @property string $des
 * @property string $receive_from
 * @property string $receive_until
 * @property int $price
 * @property string $blog_name
 * @property int $customer_id
 *
 * @property Basket[] $baskets
 * @property Customer $customer
 */
class Invoice extends ActiveRecord
{

    const STATUS_UNVERIFIED = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_CUSTOMER_DELETED_UNVERIFIED = 2;
    const STATUS_CUSTOMER_DELETED_VERIFIED = 3;
    const STATUS_ADMIN_DELETED_UNVERIFIED = 4;
    const STATUS_ADMIN_DELETED_VERIFIED = 5;

    public static function statuses()
    {
        return [
            self::STATUS_UNVERIFIED => Yii::t('app', 'Unverified'),
            self::STATUS_VERIFIED => Yii::t('app', 'Verified'),
            self::STATUS_CUSTOMER_DELETED_UNVERIFIED => Yii::t('app', 'Customer Deleted Unverified'),
            self::STATUS_CUSTOMER_DELETED_VERIFIED => Yii::t('app', 'Customer Deleted Verified'),
            self::STATUS_ADMIN_DELETED_UNVERIFIED => Yii::t('app', 'Admin Deleted Unverified'),
            self::STATUS_ADMIN_DELETED_VERIFIED => Yii::t('app', 'Admin Deleted Verified'),
        ];
    }

    public static function statuseLabel($item)
    {
        $list = self::statuses();
        return (isset($list[$item]) ? $list[$item] : null);
    }

    public static function tableName()
    {
        return 'invoice';
    }

    public function rules()
    {
        return [
            [['!status', '!blog_name', '!customer_id', '!price', 'name', 'province', 'address', 'mobile', 'phone'], 'required'],
            [['name'], 'string', 'max' => 31],
            [['province'], 'in', 'range' => array_keys(Province::getList())],
            [['address'], 'string', 'max' => 1024],
            [['mobile'], 'match', 'pattern' => '/^09[0-9]{9}$/'],
            [['phone'], 'match', 'pattern' => '/^[1-9]{1}[0-9]{5,8}$/'],
            [['des'], 'string', 'max' => 1024],
            //
            [['receive_from', 'receive_until'], 'string', 'max' => 24],
        ];
    }

    public function setPriceByArrayOfBasketsAndPackages($baskets, $packages)
    {
        $price = 0;
        $packages = ArrayHelper::index($packages, 'id');
        try {
            foreach ($baskets as $basket) {
                $basket = (array) $basket;
                $package = (array) $packages[$basket['package_id']];
                $price = $price + ($basket['cnt'] * $package['price']);
            }
        } catch (Exception $ex) {
            
        }
        return $this->price = $price;
    }

    /**
     * @return ActiveQuery
     */
    public function getBaskets()
    {
        return $this->hasMany(Basket::className(), ['invoice_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

}
