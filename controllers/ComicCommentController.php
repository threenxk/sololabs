<?php
namespace admin\controllers;

use Yii;
use admin\models\comic\ComicComment;

class ComicCommentController extends BaseController
{
    public $name = '评论';

    public $modelClass = 'admin\models\comic\ComicComment';
    public $searchModelClass = 'admin\models\comic\ComicCommentSearch';


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

        ComicComment::updateAll(['status' => ComicComment::STATUS_YES], ['id' => $id]);
        return $this->redirect(Yii::$app->request->referrer);
    }
}
