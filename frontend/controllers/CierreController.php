<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Cierre;
use frontend\models\CierrePlataforma;
use frontend\models\CierreSearch;
use frontend\models\Caja;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CierreController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new CierreSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Cierre();

        // Fechas por defecto: desde el día siguiente al último cierre, hasta hoy
        $ultimoCierre = Cierre::getUltimoCierre();
        if ($ultimoCierre) {
            $desde = date('Y-m-d', strtotime($ultimoCierre->fecha_hasta . ' +1 day'));
        } else {
            // Si no hay cierre previo, desde el registro más antiguo de caja
            $primerCaja = Caja::find()->orderBy(['fecha' => SORT_ASC])->one();
            $desde = $primerCaja ? $primerCaja->fecha : date('Y-m-d');
        }
        $model->fecha_desde = $desde;
        $model->fecha_hasta  = date('Y-m-d');

        $plataformas = CierrePlataforma::getPlataformasDefault();
        $montos = array_fill_keys($plataformas, 0);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->load($post);

            // Montos ingresados por plataforma
            $montosPost = isset($post['montos']) ? $post['montos'] : [];
            foreach ($plataformas as $p) {
                $montos[$p] = isset($montosPost[$p]) ? (int)$montosPost[$p] : 0;
            }

            // Saldo del sistema: ingresos - egresos de caja en el período
            $ingresos = (int) Caja::find()
                ->select('SUM(monto)')
                ->where(['tipo' => 0])
                ->andWhere(['>=', 'fecha', $model->fecha_desde])
                ->andWhere(['<=', 'fecha', $model->fecha_hasta])
                ->scalar();

            $egresos = (int) Caja::find()
                ->select('SUM(monto)')
                ->where(['tipo' => 1])
                ->andWhere(['>=', 'fecha', $model->fecha_desde])
                ->andWhere(['<=', 'fecha', $model->fecha_hasta])
                ->scalar();

            $model->saldo_sistema = $ingresos - $egresos;
            $model->saldo_real    = array_sum($montos);
            $model->diferencia    = $model->saldo_real - $model->saldo_sistema;
            $model->created_at    = date('Y-m-d H:i:s');

            if ($model->save()) {
                foreach ($montos as $plataforma => $monto) {
                    $cp             = new CierrePlataforma();
                    $cp->id_cierre  = $model->id;
                    $cp->plataforma = $plataforma;
                    $cp->monto      = $monto;
                    $cp->save();
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model'      => $model,
            'plataformas' => $plataformas,
            'montos'     => $montos,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Cierre anterior para comparación
        $anterior = Cierre::find()
            ->where(['<', 'id', $id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $this->render('view', [
            'model'    => $model,
            'anterior' => $anterior,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // Las plataformas se eliminan por CASCADE
        $model->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = Cierre::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('El cierre no existe.');
        }
        return $model;
    }
}
