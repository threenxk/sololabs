<?php
namespace admin\controllers;

use admin\models\welfare\WheelAwardDetail;
use admin\models\welfare\WheelAwardDetailSearch;
use \Yii;
use yii\web\Controller;

/**
 * 转盘活动
 */
class WheelAwardDetailController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new WheelAwardDetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 处理
     */
    public function actionHandel()
    {
        $params = Yii::$app->request->post();

        $model = WheelAwardDetail::findOne($params['id']);
        if (!$model) {
            return;
        }

        // 赋值补齐资料，每个状态都需要补齐资料
        $model->user_name = $params['name'];
        $model->user_mobile = $params['mobile'];
        $model->user_address = $params['address'];

        switch ($params['type']) {
            case 1: // 去发放，状态变成发放中
                if ($model->status != WheelAwardDetail::STATUS_WAITING) { // 非待发放状态直接返回
                    return false;
                }
                $model->status = WheelAwardDetail::STATUS_DISTRIBUTING;
                break;
            case 2: // 已发放，状态变成已发放
                if ($model->status != WheelAwardDetail::STATUS_DISTRIBUTING) { // 非发放中的状态直接返回
                    return false;
                }
                $model->status = WheelAwardDetail::STATUS_DISTRIBUTED;
                break;
            case 3: // 重新发放
                if ($model->status != WheelAwardDetail::STATUS_LOSE_EFFICACY) { // 非已失效的状态直接返回
                    return false;
                }

                $model->status = WheelAwardDetail::STATUS_DISTRIBUTING;
                break;
            case 4: // 编辑
                if ($model->status != WheelAwardDetail::STATUS_DISTRIBUTING) {
                    return false;
                }

                break;
        }

        if (!$model->save()) {
            Yii::warning($model->errors);
        }
    }

    /**
     * 更新奖品状态
     */
    public function actionWheelAwardDetailUpdate()
    {
        $param = Yii::$app->request->post();

        return WheelAwardDetail::updateAll(['status' => $param['status'], 'user_name' => $param['name'], 'user_mobile' => $param['mobile'], 'user_address' => $param['address']], ['id' => $param['id']]);
    }
}
