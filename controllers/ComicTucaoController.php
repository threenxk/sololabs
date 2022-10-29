<?php
namespace admin\controllers;

use admin\models\comic\ComicTucao;
use Yii;
class ComicTucaoController extends BaseController
{
    public $name = '吐槽';

    public $modelClass = 'admin\models\comic\ComicTucao';
    public $searchModelClass = 'admin\models\comic\ComicTucaoSearch';

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

        ComicTucao::updateAll(['status' => ComicTucao::STATUS_YES], ['id' => $id]);
        return $this->redirect(Yii::$app->request->referrer);
    }

}
