<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CierreSearch extends Cierre
{
    public function rules()
    {
        return [
            [['id', 'saldo_sistema', 'saldo_real', 'diferencia'], 'integer'],
            [['fecha_desde', 'fecha_hasta', 'created_at', 'observaciones'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Cierre::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['>=', 'fecha_desde', $this->fecha_desde])
              ->andFilterWhere(['<=', 'fecha_hasta', $this->fecha_hasta]);

        return $dataProvider;
    }
}
