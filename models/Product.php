<?php

namespace app\models;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $updated_at
 * @property int $created_at
 * @property string $status
 * @property string $title
 * @property double $price_min
 * @property double $price_max
 * @property string $image
 * @property int $category_id
 * @property string $blog_name
 *
 * @property Gallery[] $galleries
 * @property Package[] $packages
 * @property Category $category
 * @property Blog $blogName
 * @property ProductField[] $productFields
 */
class Product extends ActiveRecord
{

    public static function tableName()
    {
        return 'product';
    }

    public function rules()
    {
        return [
            [['status', 'title', '!category_id'], 'required'],
            [['title'], 'string', 'max' => 64],
            [['status'], 'in', 'range' => Status::getDefaultKeys()],
            [['!blog_name'], 'required'],
        ];
    }

    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['product_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

}
