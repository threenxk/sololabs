<?php

namespace admin\controllers;

use admin\models\pay\RechargeRate;
use Yii;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * RechargerateController implements the CRUD actions for RechargeRate model.
 */
class RechargerateController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all RechargeRate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => RechargeRate::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single RechargeRate model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    

    /**
     * Updates an existing RechargeRate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * NOTE: 修改汇率
     * User: hjl
     * @return array
     */
    public function actionUpdateRate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $rate = Yii::$app->request->post('rate', 0);
        $status = Yii::$app->request->post('status');

        $ret = [
            'error' => 0,
            'msg' => '',
        ];

        if (empty($id)) {
            $ret = [
                'error' => 1,
                'msg' => '请求错误',
            ];
            return $ret;
        }

        $record = RechargeRate::findOne(['id' => $id]);

        if (!$record) {
            $ret = [
                'error' => 1,
                'msg' => '请求错误',
            ];
            return $ret;
        }

        if ($rate) {
            $record->rate = $rate;
        }

        if ($status) {
            if ($status == 'true') {
                $record->status = RechargeRate::STATUS_ENABLED;
            } else {
                $record->status = RechargeRate::STATUS_DISABLED;
            }
        }

        if (!$record->save()) {
            $ret = [
                'error' => 2,
                'msg' => json_encode($record->errors, JSON_UNESCAPED_UNICODE),
            ];
            return $ret;
        }

        return $ret;
    }
    

    /**
     * Finds the RechargeRate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RechargeRate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RechargeRate::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
