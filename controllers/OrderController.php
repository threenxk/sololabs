<?php
namespace admin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * 订单管理
 */
class OrderController extends Controller
{
    /**
     * 订单列表
     * @param string $type
     * @return array
     * @throws
     */
    public function actionIndex($type)
    {
        $types = [
            'recharge'  => ['class' => 'admin\models\pay\OrderSearch',        'label' => '充值订单'],
            'baoyue'    => ['class' => 'admin\models\pay\VipBuySearch',       'label' => '会员订单'],
//            'booking'   => ['class' => 'admin\models\BookingOrderSearch', 'label' => '章节订阅'],
        ];

        if (!isset($types[$type])) {
            throw new NotFoundHttpException;
        }

        $searchModel = new $types[$type]['class'];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $tpl = 'order';

        if ($type == 'recharge') {
            $tpl = 'order';
        } else {
            $tpl = 'vip-order';
        }

        return $this->render('/'.$tpl.'/index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'types'        => $types,
            'cur_type'     => $type,
        ]);
    }
}
