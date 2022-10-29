<?php
namespace admin\controllers;

use admin\models\push\PushRemind;
use Yii;
use yii\web\Controller;

/**
 * 推送提醒管理
 */
class PushRemindController extends Controller
{
    public function actionIndex()
    {
        $pushRemind = PushRemind::find()->all();
        return $this->render('index', [
            'pushRemind' => $pushRemind
        ]);
    }

    public function actionUpdateStatus()
    {
        $status = Yii::$app->request->get('status');
        $pushId = Yii::$app->request->get('push_id');
        $pushRemind = PushRemind::findOne($pushId);
        if ($pushRemind) {
            $pushRemind->status = $status;
            if (!$pushRemind->save()) {
                Yii::warning($pushRemind->errors);
            }
        }
    }

}
