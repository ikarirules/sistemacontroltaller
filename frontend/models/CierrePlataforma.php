<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class CierrePlataforma extends ActiveRecord
{
    public static function tableName()
    {
        return 'cierre_plataforma';
    }

    public function rules()
    {
        return [
            [['id_cierre', 'plataforma'], 'required'],
            [['id_cierre', 'monto'], 'integer'],
            [['plataforma'], 'string', 'max' => 50],
        ];
    }

    public function getCierre()
    {
        return $this->hasOne(Cierre::class, ['id' => 'id_cierre']);
    }

    public static function getPlataformasDefault()
    {
        return ['BN', 'MP', 'NX', 'UALA', 'EFECTIVO'];
    }
}
