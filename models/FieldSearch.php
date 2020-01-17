<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Field;

/**
 * FieldSearch represents the model behind the search form of `app\models\Field`.
 */
class FieldSearch extends Field
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'updated_at', 'seq', 'in_summary'], 'integer'],
            [['title', 'type', 'filter'], 'safe'],
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
        $query = Field::find();
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
        $query = self::userValidQuery()->andWhere(['category_id' => $parentModel->id]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'seq' => SORT_DESC,
                    'id' => SORT_ASC,
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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'updated_at' => $this->updated_at,
            'seq' => $this->seq,
            'in_summary' => $this->in_summary,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
                ->andFilterWhere(['like', 'type', $this->type])
                ->andFilterWhere(['like', 'filter', $this->filter]);

        return $dataProvider;
    }

}
