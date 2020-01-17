<?php
namespace app\models;

use app\models\Blog;
use app\models\Gallery;
use yii\base\Model;
use app\models\Product;
use app\models\Status;
use Yii;
use yii\data\ActiveDataProvider;
use yii\base\Exception;

/**
 * GallerySearch represents the model behind the search form of `app\models\Gallery`.
 */
class GallerySearch extends Gallery
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public static function userValidQuery($id = null)
    {
        $query = Gallery::find();
        $query->andWhere(['status' => Status::getDefaultKeys(),]);
        $query->andWhere(['blog_name' => Yii::$app->blog->name(),]);
        $query->andFilterWhere(['name' => $id]);
        return $query;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $parentModel)
    {
        if ($parentModel instanceof Product) {
            $query = self::userValidQuery()->andWhere(['type' => Gallery::TYPE_PRODUCT])->andWhere(['product_id' => $parentModel->id]);
        } elseif ($parentModel instanceof Blog) {
            $query = self::userValidQuery()->andWhere(['type' => Gallery::TYPE_LOGO])->andWhere(['blog_name' => $parentModel->name]);
        } else {
            throw new Exception;
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

}
