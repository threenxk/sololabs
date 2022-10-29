<?php
namespace admin\controllers;

use admin\models\pay\ExpendSearch;
use yii\web\Controller;
use Yii;

class ExpendController extends Controller
{
    /**
     * 订单列表
     * @return array
     * @throws
     */
    public function actionIndex()
    {
        $searchModel = new ExpendSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionBookIndex()
    {
        $searchModel = new BookPaidSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('book_index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
