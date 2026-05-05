<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

class Cierre extends ActiveRecord
{
    public static function tableName()
    {
        return 'cierre';
    }

    public function rules()
    {
        return [
            [['fecha_desde', 'fecha_hasta'], 'required'],
            [['fecha_desde', 'fecha_hasta'], 'date', 'format' => 'php:Y-m-d'],
            [['saldo_sistema', 'saldo_real', 'diferencia'], 'integer'],
            [['observaciones'], 'string', 'max' => 500],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'fecha_desde'   => 'Desde',
            'fecha_hasta'   => 'Hasta',
            'saldo_sistema' => 'Saldo Sistema',
            'saldo_real'    => 'Saldo Real',
            'diferencia'    => 'Diferencia',
            'observaciones' => 'Observaciones',
            'created_at'    => 'Creado',
        ];
    }

    public function getPlataformas()
    {
        return $this->hasMany(CierrePlataforma::class, ['id_cierre' => 'id']);
    }

    public static function getUltimoCierre()
    {
        return static::find()->orderBy(['id' => SORT_DESC])->one();
    }
}
