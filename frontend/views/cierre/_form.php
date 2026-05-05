<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Cierre $model */
/** @var string[] $plataformas */
/** @var array $montos */
?>

<div class="cierre-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row mb-3">
        <div class="col-md-3">
            <?= $form->field($model, 'fecha_desde')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fecha_hasta')->textInput(['type' => 'date']) ?>
        </div>
    </div>

    <h5 class="mt-3 mb-3">Saldo por plataforma</h5>

    <table class="table table-bordered" style="max-width:400px;">
        <thead class="table-light">
            <tr>
                <th>Plataforma</th>
                <th>Monto ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plataformas as $plataforma): ?>
            <tr>
                <td><strong><?= Html::encode($plataforma) ?></strong></td>
                <td>
                    <?= Html::input('number', "montos[$plataforma]", $montos[$plataforma], [
                        'class' => 'form-control',
                        'min'   => 0,
                        'step'  => 1,
                        'id'    => 'monto_' . $plataforma,
                    ]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mb-3">
        <?= $form->field($model, 'observaciones')->textarea(['rows' => 3]) ?>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar Cierre', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary ms-2']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
