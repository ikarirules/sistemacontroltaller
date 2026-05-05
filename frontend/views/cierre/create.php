<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Cierre $model */
/** @var string[] $plataformas */
/** @var array $montos */

$this->title = 'Nuevo Cierre';
$this->params['breadcrumbs'][] = ['label' => 'Cierres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="cierre-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'      => $model,
        'plataformas' => $plataformas,
        'montos'     => $montos,
    ]) ?>
</div>
