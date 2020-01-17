<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property int $id
 * @property int $cnt
 * @property string $blog_name
 * @property int $customer_id
 * @property int $package_id
 * @property int $invoice_id
 *
 * @property Customer $customer
 * @property Package $package
 * @property Invoice $invoice
 * @property Blog $blogName
 */
class Basket extends ActiveRecord
{

    public static $activeCategories = [];
    public $package = null;

    public static function tableName()
    {
        return 'basket';
    }

    public function rules()
    {
        return [
            ['cnt', 'integer', 'min' => 1],
            ['!package_id', 'packageValidation'],
            ['!package_id', 'unique', 'targetAttribute' => ['customer_id', 'package_id', 'blog_name', 'invoice_id']],
            [['!customer_id', '!package_id', '!blog_name'], 'required'],
        ];
    }

    public static function findDuplicate($customerId, $packageId)
    {
        return self::find()->where(['invoice_id' => null])->andWhere(['customer_id' => $customerId])->andWhere(['package_id' => $packageId])->one();
    }

    public function packageValidation($attribute, $params)
    {
        $package = Package::getActivePackagesQueryByCategories(self::$activeCategories)->andWhere(['id' => $this->package_id])->one();
        if ($package) {
            return $this->package = $package;
        }
        $this->addError($attribute, Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)]));
        return $this->package = null;
    }

}
