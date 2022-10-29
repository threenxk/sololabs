<?php
namespace admin\controllers;

use admin\models\audio\AudioComment;
use Yii;
use yii\web\Controller;

class AudioCommentController extends BaseController
{
    public $name = '评论';
    public $modelClass = 'admin\models\audio\AudioComment';
    public $searchModelClass = 'admin\models\audio\AudioCommentSearch';


    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        return [];
    }

    /**
     * 审核操作
     * @return \yii\web\Response
     */
    public function actionReview() {
        $id = Yii::$app->request->get('id');

        AudioComment::updateAll(['status' => AudioComment::STATUS_YES], ['id' => $id]);
        return $this->redirect(Yii::$app->request->referrer);
    }
}
