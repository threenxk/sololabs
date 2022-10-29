<?php
namespace admin\controllers;

use admin\models\audio\AudioReadLogSearch;
use admin\models\book\BookReadLogSearch;
use admin\models\comic\ComicReadLogSearch;
use Yii;
use yii\web\Controller;

/**
 * 阅读历史记录
 * Class ReadLogController
 * @package admin\controllers
 */
class ReadLogController extends Controller
{
    public function actionBook()
    {
        $searchModel = new BookReadLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('book', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'times'        => $searchModel::$times,
        ]);
    }

    public function actionComic()
    {
        $searchModel = new ComicReadLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('comic', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'times'        => $searchModel::$times,
        ]);
    }

    public function actionAudio()
    {
        $searchModel = new AudioReadLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('audio', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'times'        => $searchModel::$times,
        ]);
    }
}
