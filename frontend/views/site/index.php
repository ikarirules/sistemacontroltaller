
<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\bootstrap5\ActiveForm;


/** @var yii\web\View $this */

$this->title = 'Dalinda Confecciones';
?>
<div class="site-index">
    <div>
      
<?php 
    $form = ActiveForm::begin(['method' => 'post']);
    
    // Supongamos que $periodos contiene las fechas en un formato simple 'YYYY-MM'
    //$periodos = ['2024-01', '2023-12', '2023-11', '2023-10', '2023-09', '2023-08', '2023-07'];

    // Construir un array asociativo sin ceros a la izquierda
    $options = array_combine($periodos, $periodos);
?>

<?= Html::dropDownList('periodo', null, $options, ['prompt' => 'Selecciona un periodo']) ?>

<?= Html::submitButton('Mostrar', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>




    </div>

<br>
    <h2> Periodo: <?php echo($periodoSelect) ?></h2>
            <?php 
   
            $dataProvider = new \yii\data\ArrayDataProvider([
                'allModels' => $totales,
                'pagination' => false,
            ]);

            echo \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter' => true,
                'rowOptions' => function ($model, $key, $index, $grid) {
                    return $index % 2 == 0 ? ['class' => 'even-row'] : ['class' => 'odd-row'];
                },
                'tableOptions' => ['class' => 'my-gridview'],
                'columns' => [

                    // Agrega aquí las columnas para las demás categorías
                      [
                        'attribute' => 'categoria',
                        'label' => 'Categoria:',
                        'footer' => 'Total',
                    ],
                      [
                        'attribute' => 'Ingreso',
                        'footer' => array_sum(array_column($totales, 'Ingreso')),
                    ],
                    [
                        'attribute' => 'Egreso',
                        'footer' => array_sum(array_column($totales, 'Egreso')),
                    ],
                    [
                        'attribute' => 'Saldo',
                        'footer' => array_sum(array_column($totales, 'Saldo')),
                    ],
                ],
            ]);

             ?>

        

        <br>
         <?php 

            $dataProvider2 = new \yii\data\ArrayDataProvider([
                'allModels' => $totalGen,
                'pagination' => false,
            ]);

            echo \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider2,
                'showFooter' => true,
                'rowOptions' => function ($model, $key, $index, $grid) {
                    return $index % 2 == 0 ? ['class' => 'even-row'] : ['class' => 'odd-row'];
                },
                'tableOptions' => ['class' => 'my-gridview'],
                'columns' => [

                    // Agrega aquí las columnas para las demás categorías
                      [
                        'attribute' => 'categoria',
                        'label' => 'Categoria:',
                        'footer' => 'Total',
                    ],
                      [
                        'attribute' => 'Ingreso',
                        'footer' => array_sum(array_column($totalGen, 'Ingreso')),
                    ],
                    [
                        'attribute' => 'Egreso',
                        'footer' => array_sum(array_column($totalGen, 'Egreso')),
                    ],
                    [
                        'attribute' => 'Saldo',
                        'footer' => array_sum(array_column($totalGen, 'Saldo')),
                    ],
                ],
            ]);

             ?>
    </div>

<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js', ['position' => \yii\web\View::POS_HEAD]);
$labels    = json_encode(array_column($chartData, 'label'));
$ingresos  = json_encode(array_column($chartData, 'ingresos'));
$egresos   = json_encode(array_column($chartData, 'egresos'));
$saldos    = json_encode(array_map(fn($m) => $m['ingresos'] - $m['egresos'], $chartData));
?>

<div style="margin-top:30px;">
    <h4>Ingresos y Egresos — últimos 18 meses</h4>
    <canvas id="chartBarras" height="100"></canvas>
</div>

<div style="margin-top:40px; margin-bottom:40px;">
    <h4>Saldo neto mensual — últimos 18 meses</h4>
    <canvas id="chartSaldo" height="80"></canvas>
</div>

<script>
(function() {
    var labels   = <?= $labels ?>;
    var ingresos = <?= $ingresos ?>;
    var egresos  = <?= $egresos ?>;
    var saldos   = <?= $saldos ?>;

    new Chart(document.getElementById('chartBarras'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresos,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: 'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Egresos',
                    data: egresos,
                    backgroundColor: 'rgba(231, 76, 60, 0.7)',
                    borderColor: 'rgba(192, 57, 43, 1)',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '$' + v.toLocaleString('es-AR')
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('chartSaldo'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Saldo neto',
                data: saldos,
                borderColor: 'rgba(52, 152, 219, 1)',
                backgroundColor: 'rgba(52, 152, 219, 0.15)',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    ticks: {
                        callback: v => '$' + v.toLocaleString('es-AR')
                    }
                }
            }
        }
    });
})();
</script>




<style type="text/css">
    /* Estilos para la tabla del GridView con la clase my-gridview */
    .my-gridview {
        width: 100%; /* Ajustar el ancho según sea necesario */
        border-collapse: collapse; /* Fusionar los bordes de las celdas */
        border: 1px solid #ccc; /* Establecer un borde alrededor de la tabla */
        border-spacing: 0; /* Espaciado entre celdas */
        background-color: #d7bde2;
        box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5);
    }

    .my-gridview th,
    .my-gridview td {
        padding: 8px; /* Espaciado interno para las celdas de encabezado y datos */
        text-align: left; /* Alinear el contenido del texto a la izquierda */
        /*background-color: #ebdef0;*/
        border: 1px solid #f5eef8; /* Agregar borde a las celdas */
    }

    /* Estilo para filas pares */
    .my-gridview tr.even-row {
        background-color: #ebdef0; /* Color de fondo para filas pares */
    }

    /* Estilo para filas impares */
    .my-gridview tr.odd-row {
        background-color:  #f4ecf7 ; /* Color de fondo para filas impares */
    }

    /* Estilos para el pie de la tabla (footer) */
    .my-gridview tfoot {
        font-weight: bold; /* Texto en negrita en el pie de la tabla */
        background-color: #d7bde2; /* Color de fondo para el pie de la tabla */
    }
    
</style>

