<?php
namespace admin\controllers;

use admin\models\data\DayStatSearch;
use Yii;
use yii\web\Controller;

class DayStatController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new DayStatSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // 导出操作
        $type = Yii::$app->request->get('type', '');
        if ($type == 'export') {    // 导出
            $exportList = $dataProvider->getModels();
            $excelData = array(
                array('日期', '安卓新增用户', 'iOS新增用户', '公众号新增', '总新增用户', '总用户',  '充值订单数', '充值订单总数', '开通会员数', '开通会员总数'),
            );

            foreach ($exportList as $info) {
                $excelData[] = array(
                    $info->date, $info->android_incr, $info->apple_incr, $info->mp_incr, $info->total_incr, $info->total_user, $info->recharge_incr, $info->recharge_total, $info->vip_incr, $info->vip_total
                );
            }

            \common\components\phpexcel\Excel::exportSimple($excelData, '日统计');
            exit;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
}
