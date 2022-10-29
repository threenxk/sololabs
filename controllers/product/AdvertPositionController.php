<?php
namespace admin\controllers\product;

use admin\models\advert\AdvertPosition;
use Yii;
use yii\web\Controller;

class AdvertPositionController extends Controller
{
   public function actionIndex()
   {
       $advertPosition = AdvertPosition::find()->all();

       return $this->render('@admin/views/product/' . $this->id . '/index', [
           'advertPosition' => $advertPosition
       ]);
   }

    public function actionUpdateStatus()
    {
        $positionId = Yii::$app->request->get('position_id');
        $stats = Yii::$app->request->get('status');

        $advertPosition = AdvertPosition::findOne($positionId);
        $advertPosition->status = $stats;
        $advertPosition->save();
    }

}
