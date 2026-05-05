<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\CierreSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Cierres';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="cierre-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Nuevo Cierre', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'fecha_desde',
            'fecha_hasta',
            [
                'attribute' => 'saldo_sistema',
                'label'     => 'Saldo Sistema',
                'value'     => fn($m) => '$' . number_format($m->saldo_sistema, 0, ',', '.'),
                'contentOptions' => ['class' => 'text-end'],
                'headerOptions'  => ['class' => 'text-end'],
            ],
            [
                'attribute' => 'saldo_real',
                'label'     => 'Saldo Real',
                'value'     => fn($m) => '$' . number_format($m->saldo_real, 0, ',', '.'),
                'contentOptions' => ['class' => 'text-end'],
                'headerOptions'  => ['class' => 'text-end'],
            ],
            [
                'attribute'      => 'diferencia',
                'label'          => 'Diferencia',
                'contentOptions' => fn($m) => ['class' => 'text-end ' . ($m->diferencia >= 0 ? 'text-success' : 'text-danger')],
                'headerOptions'  => ['class' => 'text-end'],
                'value'          => fn($m) => ($m->diferencia >= 0 ? '+' : '') . '$' . number_format(abs($m->diferencia), 0, ',', '.'),
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons'  => [
                    'delete' => fn($url, $model) => Html::a(
                        '<span class="bi bi-trash"></span>',
                        $url,
                        [
                            'title' => 'Eliminar',
                            'data'  => ['confirm' => '¿Eliminar este cierre?', 'method' => 'post'],
                        ]
                    ),
                ],
            ],
        ],
    ]); ?>
</div>
