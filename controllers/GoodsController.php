<?php

namespace admin\controllers;

use admin\models\pay\BuyActivity;
use admin\models\pay\Goods;
use admin\models\pay\GoodsSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * GoodsController implements the CRUD actions for Goods model.
 */
class GoodsController extends Controller
{
    /**
     * Lists all Goods models.
     * @return mixed
     */
    public function actionIndex()
    {
        $typeStr = Yii::$app->request->get('type');
        $type = isset(Goods::$typeMap[$typeStr]) ? Goods::$typeMap[$typeStr] : Goods::$typeMap['default'];

        $searchModel = new GoodsSearch();
        $searchModel->type = $type;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render($typeStr.'/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new Goods model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $typeStr = Yii::$app->request->get('type');
        $type = isset(Goods::$typeMap[$typeStr]) ? Goods::$typeMap[$typeStr] : Goods::$typeMap['default'];

        $model = new Goods();
        $model->type = $type;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->from_channel == Goods::FROM_CHANNEL_IOS && !$model->apple_id) { // 如果是苹果商品，且未设置苹果信息
                $model->addError('apple_id', '苹果商品id必填');
                return $this->render($typeStr.'/create', [
                    'model' => $model,
                ]);
            }

            // 设置条件，保证顺序正确
            if ($model->save()) {
                return $this->redirect(['goods/'.$typeStr   ]);
            } else {
                Yii::warning($model->errors);
            }
        }

        return $this->render($typeStr.'/create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Goods model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $map = array_flip(Goods::$typeMap);

        $tplDir = isset($map[$model->type]) ? $map[$model->type] : $map[Goods::TYPE_RECHARGE];

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect([$tplDir]);
            }
        }

        return $this->render($tplDir.'/update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $map = array_flip(Goods::$typeMap);

        $tplDir = isset($map[$model->type]) ? $map[$model->type] : $map[Goods::TYPE_RECHARGE];

        // 删除关联的活动
        BuyActivity::deleteAll(['goods_id' => $id]);

        $model->delete();

        return $this->redirect([$tplDir]);
    }

    /**
     * Finds the Goods model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Goods the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Goods::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 设置位置
     */
    public function actionSetPosition()
    {
        $id = Yii::$app->request->get('id');
        $order = intval(Yii::$app->request->get('order')); // 序号

        if (!$order) { // 异常序号
            return $this->redirect(Yii::$app->request->referrer);
        }

        // 找到自己这条记录
        $selfModel = Goods::findOne($id);
        if (!$selfModel) { // 异常数据返回
            return $this->redirect(Yii::$app->request->referrer);
        }

        $selfModel->setPosition($order);
    }
}
