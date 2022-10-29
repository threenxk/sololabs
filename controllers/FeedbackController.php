<?php
namespace admin\controllers;

use admin\models\feedback\Feedback;
use admin\models\feedback\FeedbackSearch;
use common\helpers\Tool;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class FeedbackController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new FeedbackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->get('id');
        $model = Feedback::findOne($id);
        if ($model) {
            $model->delete();
        }

        return $this->redirect('index');
    }

    /**
     * 批量删除
     * @return string
     */
    public function actionBatchDel()
    {
        $ids    = Yii::$app->request->post('ids');

        $result = Feedback::deleteAll(['id' => $ids]);

        exit($result===false ? '0' : '1');
    }


    public function actionReply()
    {
        $id = Yii::$app->request->get('id');
        $replyContent = Yii::$app->request->get('reply_content'); // 回复内容
        $feedback = Feedback::findOne(['id' => $id]);
        if (!$feedback) {
            return Tool::responseJson(1, '操作失败!');
        }

        $feedback->reply = $replyContent;
        $feedback->admin_id = Yii::$app->user->identity->id;
        $feedback->save();

        return Tool::responseJson(0, '操作成功!');
    }

}
