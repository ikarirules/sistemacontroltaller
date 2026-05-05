<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\Caja;
use frontend\models\CajaSearch;
use frontend\models\Categoria;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $modelCategoria = new Categoria();
        $modelCategorias = Categoria::find()->all();


        $periodos = $this->actionListaFechas();
        //var_dump($periodos); die();

        if (Yii::$app->request->post()) {

            $periodoSelect = Yii::$app->request->post('periodo');
            $anio = substr($periodoSelect, 0, 4); // Obtiene los primeros 4 caracteres (el año)
            $mesActual = substr($periodoSelect, 5, 2);
            //var_dump($mesActual); die();
        }else{

            $mesActual = date('m');
            $anio = date('Y');
            //var_dump($mesActual);    
            $periodoSelect = $mesActual.'-'.$anio;
        }
        
        $totalMesAct = [];
        $totalGen = [];
        foreach($modelCategorias as $categoria){
            $categoriaArray = [];
            $querySumMesAct = Caja::find()
                ->select('SUM(monto) AS total')
                ->where(['id_categoria' => $categoria['id'], 'tipo' => 0])
                ->andWhere(['MONTH(fecha)' => $mesActual])
                ->andWhere(['YEAR(fecha)' => $anio]);
            $totalSumMesAct = $querySumMesAct->createCommand()->queryScalar();

            $queryDifMesAct = Caja::find()
                ->select('SUM(monto) AS total')
                ->where(['id_categoria' => $categoria['id'], 'tipo' => 1])
                ->andWhere(['MONTH(fecha)' => $mesActual])
                ->andWhere(['YEAR(fecha)' => $anio]);
            $totalDifMesAct = $queryDifMesAct->createCommand()->queryScalar();

            $categoriaArray['categoria'] = $categoria['descripcion'];
            $categoriaArray['Ingreso'] = $totalSumMesAct;
            $categoriaArray['Egreso'] = $totalDifMesAct;
            $categoriaArray['Saldo'] = $totalSumMesAct - $totalDifMesAct;
            $totalMesAct[$categoria['descripcion']] = $categoriaArray;


            $categoriaArrayGen = [];
            $querySum = Caja::find()
                ->select('SUM(monto) AS total')
                ->where(['id_categoria' => $categoria['id'], 'tipo' => 0]);
            $totalSum = $querySum->createCommand()->queryScalar();

            $queryDif = Caja::find()
                ->select('SUM(monto) AS total')
                ->where(['id_categoria' => $categoria['id'], 'tipo' => 1]);
            $totalDif = $queryDif->createCommand()->queryScalar();

            $categoriaArrayGen['categoria'] = $categoria['descripcion'];
            $categoriaArrayGen['Ingreso'] = $totalSum;
            $categoriaArrayGen['Egreso'] = $totalDif;
            $categoriaArrayGen['Saldo'] = $totalSum - $totalDif;
            $totalGen[$categoria['descripcion']] = $categoriaArrayGen;      
        }

        // Últimos 18 meses para el gráfico
        $fechaDesde = date('Y-m-01', strtotime('-17 months'));
        $nombresMeses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $meses18 = [];
        for ($i = 17; $i >= 0; $i--) {
            $key   = date('Y-m', strtotime("-$i months"));
            $partes = explode('-', $key);
            $meses18[$key] = [
                'label'    => $nombresMeses[(int)$partes[1]] . ' ' . $partes[0],
                'ingresos' => 0,
                'egresos'  => 0,
            ];
        }

        $ingresosRaw = Caja::find()
            ->select(["DATE_FORMAT(fecha, '%Y-%m') AS periodo", 'SUM(monto) AS total'])
            ->where(['tipo' => 0])
            ->andWhere(['>=', 'fecha', $fechaDesde])
            ->groupBy(['periodo'])
            ->asArray()
            ->all();

        $egresosRaw = Caja::find()
            ->select(["DATE_FORMAT(fecha, '%Y-%m') AS periodo", 'SUM(monto) AS total'])
            ->where(['tipo' => 1])
            ->andWhere(['>=', 'fecha', $fechaDesde])
            ->groupBy(['periodo'])
            ->asArray()
            ->all();

        foreach ($ingresosRaw as $row) {
            if (isset($meses18[$row['periodo']])) {
                $meses18[$row['periodo']]['ingresos'] = (int)$row['total'];
            }
        }
        foreach ($egresosRaw as $row) {
            if (isset($meses18[$row['periodo']])) {
                $meses18[$row['periodo']]['egresos'] = (int)$row['total'];
            }
        }

        $chartData = array_values($meses18);

        return $this->render('index', [
            'periodos'    => $periodos,
            'totales'     => $totalMesAct,
            'totalGen'    => $totalGen,
            'chartData'   => $chartData,
            'periodoSelect' => $periodoSelect,
        ]);

    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
    
    public function actionBuscar()
    {
        $selectedMonth = Yii::$app->request->get('selectedMonth');
        
        // Validación de entrada y lógica de búsqueda
        if ($selectedMonth) {
            // Realizar una consulta a la base de datos, por ejemplo, utilizando Active Record de Yii:
            $results = TuModelo::find()
                ->where(['like', 'tuCampoFecha', '02-' . $selectedMonth])
                ->all();
            
            // Haz algo con los resultados (por ejemplo, muestra una vista con los resultados)
            return $this->render('resultados', ['results' => $results]);
        } else {
            // No se ha seleccionado un mes, puedes mostrar un mensaje de error o redirigir a otra página.
        }
    }
    
    public function actionListaFechas(){
        $fechas = Caja::find()
            ->select([
                'MONTH(fecha) AS mes',
                'YEAR(fecha) AS ano'
            ])
            ->distinct()
            ->orderBy(['ano' => SORT_DESC, 'mes' => SORT_DESC]) // Opcional: Ordenar por año y mes
            ->asArray()
            ->all();

        // Obtener un array de mes/año únicos sin repeticiones
        $uniqueMonthsYears = array_map(
            function ($item) {
                return $item['ano'] . '-' . str_pad($item['mes'], 2, '0', STR_PAD_LEFT);
            },
            $fechas
        );
        $fechaConIndice = [];
        foreach ($uniqueMonthsYears as $key) {
            $fechaConIndice[$key] = $key;
        }

        //echo('<pre>');
        //var_dump($fechaConIndice); die();
        return $fechaConIndice;
    }

    public function actionGrafo()
    {
        $periodos = $this->actionListaFechas();
        var_dump($periodos); die();

        $gastosTaller='qwe';
        // Obtener datos para el gráfico
        $gastosTaller = Taller::find()->where(['tipo' => 'gasto'])->groupBy(['MONTH(fecha)'])->sum('monto');
        $gastosHabituales = Taller::find()->where(['tipo' => 'gasto_habitual'])->groupBy(['MONTH(fecha)'])->sum('monto');
        $ingresos = Taller::find()->where(['tipo' => 'ingreso'])->groupBy(['MONTH(fecha)'])->sum('monto');

        // Convertir los datos a formato adecuado para el gráfico
        $labels = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $data = [
            'Gastos de Taller' => array_values($gastosTaller),
            'Gastos Habituales' => array_values($gastosHabituales),
            'Ingresos' => array_values($ingresos),
        ];

        // Renderizar la vista con el gráfico
        return $this->render('grafico', [
            'labels' => $labels,
            'data' => $data,
        ]);
    }
    public function actionGrafico(){

        $categorias = $this->obtenerCategorias();
        $categoriasNombres = $this->obtenerNombreCategoria();
        $periodos = $this->actionListaFechas();
       // var_dump($periodos); die();
        
        $periodosPorAnio = [];

        foreach ($periodos as $key => $value) {
            $anio = substr($key, 0, 4); // extrae '2024' de '2024-12'
            if (!isset($periodosPorAnio[$anio])) {
                $periodosPorAnio[$anio] = [];
            }
            $periodosPorAnio[$anio][$key] = $value;
        }
        $anioActual = date('Y');
        $anioSeleccionado = Yii::$app->request->post('anio', $anioActual);
        $periodosDelAnio = $periodosPorAnio[$anioSeleccionado] ?? [];

        $anios = [];
        foreach (array_keys($periodos) as $fecha) {
            $anio = substr($fecha, 0, 4);
            $anios[$anio] = $anio;
        }
        krsort($anios);
        $anioDefault = array_key_first($anios);
        $anioSeleccionado = Yii::$app->request->post('anio', $anioDefault);
        $periodosFiltrados = array_filter($periodos, function($periodo) use ($anioSeleccionado) {
            return strpos($periodo, $anioSeleccionado . '-') === 0;
        });
        //var_dump($periodosFiltrados); die();
        $elTodo = [];
        $contador = 0;
        foreach ($categorias as $cat) {
            $categoriaArray = [
                'id_categoria' => $cat['id_categoria'],
                'tipo' => $cat['tipo'],
                'totales' => [],
            ];
            $periodos = $periodosFiltrados;
            foreach ($periodos as $period) {
                //$anio = substr($period, 0, 4);
                $anio = $anioSeleccionado;
                $mesActual = substr($period, 5, 2);

                $totalPeriodo = Caja::find()
                    ->select('SUM(monto) AS total')
                    ->where(['id_categoria' => $cat['id_categoria'], 'tipo' => $cat['tipo']])
                    ->andWhere(['MONTH(fecha)' => $mesActual])
                    ->andWhere(['YEAR(fecha)' => $anio])
                    ->scalar();

                $categoriaArray['totales'][] = $totalPeriodo;
            }

            $elTodo[$categoriasNombres[$contador]] = $categoriaArray['totales'];
            $contador=$contador +1;
        }

        /*echo('<pre>');
        var_dump($elTodo);
        echo('<pre>');
        var_dump($categoriasNombres); die();*/
        $data = [];
        $labels = range(1, count(reset($elTodo))); // Suponiendo que hay el mismo número de períodos en cada categoría

        foreach ($elTodo as $categoria => $totales) {
            $dataset = [
                'label' => $categoria,
                'backgroundColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.2)', // Colores aleatorios
                'borderColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 1)',
                'borderWidth' => 2,
                'data' => $totales,
                'fill' => false,
                'tension' => 0,
            ];

            $data['datasets'][] = $dataset;
        }

        $data['labels'] = $labels;
        return $this->render('grafico', [
            'labels' => $periodos,
            'data' => $data,
            'anios' => $anios,
            'anioSeleccionado' => $anioSeleccionado
        ]);

    }

    private function obtenerCategorias(){
        $combinacionesUnicas = Caja::find()
            ->select(['id_categoria', 'tipo'])
            ->distinct()
            ->groupBy(['id_categoria', 'tipo'])
            ->asArray()
            ->all();
        return $combinacionesUnicas;
    }

    private function obtenerNombreCategoria(){
        $categorias = $this->obtenerCategorias();
        $nombreCategoria = [];
        foreach ($categorias as $value) {
            //var_dump($value); die();
            
            if ($value['tipo'] == '0') {
                $tipo = 'Ingreso';    
            }
            if ($value['tipo'] == '1') {
                $tipo = 'Egreso';    
            }
            
            $categoriaNombre = Categoria::findOne($value['id_categoria']);
            if ($categoriaNombre !== null) {
                $nomCat = $categoriaNombre->descripcion;
                // Haz algo con $nombreCategoria
            } else {
                // Manejar el caso en el que no se encuentra la categoría con el ID dado
                echo "Categoría no encontrada para el ID: $categoriaNombre";
            }

            $nombreCategoria[] =  $tipo.'-'.$nomCat;
        } 
        return $nombreCategoria;
    }
}
