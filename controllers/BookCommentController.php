<?php
namespace admin\controllers;

use admin\models\book\BookComment;
use admin\models\book\BookCommentSearch;
use common\helpers\RedisKey;
use common\helpers\Tool;
use Yii;
use yii\web\Controller;

class BookCommentController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new BookCommentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionChapter()
    {
        $searchModel = new BookCommentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('chapter', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->get('id');
        BookComment::deleteAll(['id' => $id]);
        BookComment::clearCache();
        return $this->redirect(Yii::$app->request->referrer);
    }

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

        BookComment::updateAll(['status' => BookComment::STATUS_YES], ['id' => $id]);
        Tool::batchClearCache(sprintf(RedisKey::BOOK_COMMENT, '')); // 评论列表缓存
        return $this->redirect(Yii::$app->request->referrer);
    }
}
