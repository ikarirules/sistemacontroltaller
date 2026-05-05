<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Cierre $model */
/** @var frontend\models\Cierre|null $anterior */

$this->title = 'Cierre #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Cierres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$difClass = $model->diferencia >= 0 ? 'text-success' : 'text-danger';
$difSigno = $model->diferencia >= 0 ? '+' : '';
?>

<div class="cierre-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-sm',
            'data'  => [
                'confirm' => '¿Eliminar este cierre?',
                'method'  => 'post',
            ],
        ]) ?>
        <?= Html::a('Volver', ['index'], ['class' => 'btn btn-secondary btn-sm ms-2']) ?>
    </p>

    <!-- Período -->
    <div class="card mb-4">
        <div class="card-header"><strong>Período</strong></div>
        <div class="card-body">
            <p class="mb-1"><strong>Desde:</strong> <?= Html::encode($model->fecha_desde) ?></p>
            <p class="mb-1"><strong>Hasta:</strong> <?= Html::encode($model->fecha_hasta) ?></p>
            <?php if ($model->observaciones): ?>
                <p class="mb-0"><strong>Obs:</strong> <?= Html::encode($model->observaciones) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Balance -->
    <div class="card mb-4">
        <div class="card-header"><strong>Balance</strong></div>
        <div class="card-body">
            <table class="table table-sm" style="max-width:350px;">
                <tr>
                    <td>Saldo sistema (caja)</td>
                    <td class="text-end"><strong>$<?= number_format($model->saldo_sistema, 0, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <td>Saldo real (plataformas)</td>
                    <td class="text-end"><strong>$<?= number_format($model->saldo_real, 0, ',', '.') ?></strong></td>
                </tr>
                <tr class="table-light">
                    <td><strong>Diferencia</strong></td>
                    <td class="text-end <?= $difClass ?>">
                        <strong><?= $difSigno . '$' . number_format(abs($model->diferencia), 0, ',', '.') ?></strong>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Plataformas -->
    <div class="card mb-4">
        <div class="card-header"><strong>Detalle por plataforma</strong></div>
        <div class="card-body">
            <table class="table table-bordered" style="max-width:350px;">
                <thead class="table-light">
                    <tr>
                        <th>Plataforma</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->plataformas as $p): ?>
                    <tr>
                        <td><?= Html::encode($p->plataforma) ?></td>
                        <td class="text-end">$<?= number_format($p->monto, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>Total</th>
                        <th class="text-end">$<?= number_format($model->saldo_real, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Comparación con cierre anterior -->
    <?php if ($anterior): ?>
    <div class="card mb-4">
        <div class="card-header"><strong>Comparación con cierre anterior (#<?= $anterior->id ?>)</strong></div>
        <div class="card-body">
            <?php
            $varSaldoReal   = $model->saldo_real - $anterior->saldo_real;
            $varDiferencia  = $model->diferencia - $anterior->diferencia;
            $varRealClass   = $varSaldoReal >= 0 ? 'text-success' : 'text-danger';
            $varDifClass    = $varDiferencia >= 0 ? 'text-success' : 'text-danger';
            $varRealSigno   = $varSaldoReal >= 0 ? '+' : '';
            $varDifSigno    = $varDiferencia >= 0 ? '+' : '';
            ?>
            <table class="table table-sm" style="max-width:420px;">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th class="text-end">Anterior</th>
                        <th class="text-end">Este cierre</th>
                        <th class="text-end">Variación</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Período hasta</td>
                        <td class="text-end"><?= $anterior->fecha_hasta ?></td>
                        <td class="text-end"><?= $model->fecha_hasta ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Saldo real</td>
                        <td class="text-end">$<?= number_format($anterior->saldo_real, 0, ',', '.') ?></td>
                        <td class="text-end">$<?= number_format($model->saldo_real, 0, ',', '.') ?></td>
                        <td class="text-end <?= $varRealClass ?>">
                            <?= $varRealSigno . '$' . number_format(abs($varSaldoReal), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Diferencia</td>
                        <td class="text-end <?= $anterior->diferencia >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= ($anterior->diferencia >= 0 ? '+' : '') . '$' . number_format(abs($anterior->diferencia), 0, ',', '.') ?>
                        </td>
                        <td class="text-end <?= $difClass ?>">
                            <?= $difSigno . '$' . number_format(abs($model->diferencia), 0, ',', '.') ?>
                        </td>
                        <td class="text-end <?= $varDifClass ?>">
                            <?= $varDifSigno . '$' . number_format(abs($varDiferencia), 0, ',', '.') ?>
                        </td>
                    </tr>

                    <?php
                    // Plataformas del cierre anterior indexadas
                    $platAnt = [];
                    foreach ($anterior->plataformas as $pa) {
                        $platAnt[$pa->plataforma] = $pa->monto;
                    }
                    foreach ($model->plataformas as $p):
                        $montoAnt = isset($platAnt[$p->plataforma]) ? $platAnt[$p->plataforma] : 0;
                        $varPlat  = $p->monto - $montoAnt;
                        $varPlatClass = $varPlat >= 0 ? 'text-success' : 'text-danger';
                        $varPlatSigno = $varPlat >= 0 ? '+' : '';
                    ?>
                    <tr>
                        <td><?= Html::encode($p->plataforma) ?></td>
                        <td class="text-end">$<?= number_format($montoAnt, 0, ',', '.') ?></td>
                        <td class="text-end">$<?= number_format($p->monto, 0, ',', '.') ?></td>
                        <td class="text-end <?= $varPlatClass ?>">
                            <?= $varPlatSigno . '$' . number_format(abs($varPlat), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <p class="text-muted">Este es el primer cierre registrado.</p>
    <?php endif; ?>
</div>
