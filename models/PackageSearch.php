<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Package;
use app\models\Status;

/**
 * PackageSearch represents the model behind the search form of `app\models\Package`.
 */
class PackageSearch extends Package
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', 'product_id'], 'integer'],
            [['status', 'guaranty', 'des', 'color'], 'safe'],
            [['price'], 'number'],
        ];
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
        $query = Package::find();
        $query->andWhere(['status' => Status::getDefaultKeys(),]);
        $query->andWhere(['blog_name' => Yii::$app->blog->name(),]);
        $query->andFilterWhere(['id' => $id]);
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
        $query = self::userValidQuery()->andWhere(['product_id' => $parentModel->id]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'price' => $this->price,
            'product_id' => $this->product_id,
            'color' => $this->color,
        ]);

        $query->andFilterWhere(['like', 'guaranty', $this->guaranty])
                ->andFilterWhere(['like', 'des', $this->des]);

        return $dataProvider;
    }

}
