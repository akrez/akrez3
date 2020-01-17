<?php

namespace app\models;

use app\models\Invoice;
use app\models\Status;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class InvoiceSearch extends Invoice
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'price'], 'integer'],
            [['status', 'name', 'province', 'address', 'mobile', 'phone', 'des'], 'safe'],
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
        $query = Invoice::find();
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
        $query = self::userValidQuery();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'price' => $this->price,
        ]);

        $query
                ->andFilterWhere(['like', 'status', $this->status])
                ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'province', $this->province])
                ->andFilterWhere(['like', 'address', $this->address])
                ->andFilterWhere(['like', 'mobile', $this->mobile])
                ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'des', $this->des]);


        return $dataProvider;
    }

}
