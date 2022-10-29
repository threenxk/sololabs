<?php
namespace admin\controllers;

use admin\models\data\IncomeSearch;
use admin\models\pay\VipBuy;
use admin\models\user\User;
use common\helpers\Tool;
use admin\models\pay\Order;
use common\models\pay\OrderStat;
use Yii;
use yii\web\Controller;

/**
 * 充值统计
 */
class IncomeController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new IncomeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $data = [
            'today' => ['normal_recharge' => 0, 'vip_recharge' => 0, 'recharge_num' => 0, 'un_recharge_num' => 0, 'vip_num' => 0, 'un_vip_num' => 0, 'recharge_rate' => 0, 'vip_rate' => 0],
            'yesterday' => ['normal_recharge' => 0, 'vip_recharge' => 0, 'recharge_num' => 0, 'un_recharge_num' => 0, 'vip_num' => 0, 'un_vip_num' => 0, 'recharge_rate' => 0, 'vip_rate' => 0],
            'month' => ['normal_recharge' => 0, 'vip_recharge' => 0, 'recharge_num' => 0, 'un_recharge_num' => 0, 'vip_num' => 0, 'un_vip_num' => 0, 'recharge_rate' => 0, 'vip_rate' => 0],
            'total' => ['normal_recharge' => 0, 'vip_recharge' => 0, 'recharge_num' => 0, 'un_recharge_num' => 0, 'vip_num' => 0, 'un_vip_num' => 0, 'recharge_rate' => 0, 'vip_rate' => 0]
        ];

        //今日订单数据处理
        $currentTime = strtotime(date('Ymd'));

        //充值订单
        $objOrderList = Order::find()
            ->joinWith('user')
            ->select(Order::tableName().'.uid,'.Order::tableName().'.total_fee,'.Order::tableName().'.status,'.User::tableName().'.created_at')
            ->andWhere(Order::tableName().'.created_at>=:start_time',
                [':start_time' => $currentTime])
            ->all();

        $orderNum = count($objOrderList); //订单数
        $sucOrderNum = 0; //成功支付订单数
        $totalFee = 0; //充值金额

        foreach ($objOrderList as $order) {
            if ($order->status == Order::STATUS_SUCCESS) {
                $sucOrderNum += 1;
                $totalFee += $order->total_fee;
            }
        }

        //支付订单
        $objVipOrderList = VipBuy::find()
            ->joinWith('user')
            ->select(VipBuy::tableName().'.uid,'.VipBuy::tableName().'.total_fee,'.VipBuy::tableName().'.status,'.User::tableName().'.created_at')
            ->andWhere(VipBuy::tableName().'.created_at>=:start_time',
                [':start_time' => $currentTime])
            ->all();

        //统计vip数据
        $sucVipOrderNum = 0;
        $vipTotalFee = 0;
        $vipOrderNum = count($objVipOrderList);

        foreach ($objVipOrderList as $vipOrder) {
            if ($vipOrder->status == VipBuy::STATUS_SUCCESS) {
                $sucVipOrderNum += 1;
                $vipTotalFee += $vipOrder->total_fee;
            }
        }
        //当日充值
        $data['today']['normal_recharge'] = $totalFee;  //普通充值
        $data['today']['vip_recharge'] = $vipTotalFee;  //vip充值
        $data['today']['recharge_num'] = $sucOrderNum;  //普通充值笔数
        $data['today']['vip_num'] = $sucVipOrderNum;           //开通vip数
        $data['today']['recharge_rate'] = $orderNum > 0 ? intval(($sucOrderNum / $orderNum) * 100) : 0; //充值完成率
        $data['today']['vip_rate'] = $vipOrderNum > 0 ? intval(($sucVipOrderNum/ $vipOrderNum) * 100) : 0; //vip充值完成率

        //昨日订单
        $yesterday = OrderStat::findOne([ 'date' => date('Ymd', strtotime("-1 day"))]);
        if ($yesterday) {
            $data['yesterday']['normal_recharge'] = $yesterday->recharge_amount;  //普通充值
            $data['yesterday']['vip_recharge'] = $yesterday->vip_amount;        //vip充值
            $data['yesterday']['recharge_num'] = $yesterday->recharge_suc_num; //普通充值笔数
            $data['yesterday']['un_recharge_num'] = $yesterday->recharge_order_num - $yesterday->recharge_suc_num; //普通充值未支付笔数
            $data['yesterday']['vip_num'] = $yesterday->vip_suc_num;     //开通vip数
            $data['yesterday']['un_vip_num'] = $yesterday->vip_order_num - $yesterday->vip_suc_num;   //开通vip未支付数
            $data['yesterday']['recharge_rate'] = $yesterday->recharge_order_num > 0 ? intval(($yesterday->recharge_suc_num / $yesterday->recharge_order_num) * 100) : 0;     //充值完成率
            $data['yesterday']['vip_rate'] = $yesterday->vip_order_num > 0 ? intval(($yesterday->vip_suc_num / $yesterday->vip_order_num) * 100) : 0;          //开通vip完成率
        }

        //月订单
        $month = OrderStat::find()
            ->where(['year_month' => date('Ym')])
            ->asArray()
            ->all();

        if ($month) {
            $data['month']['normal_recharge'] = array_sum(array_column($month, 'recharge_amount'));  //普通充值
            $data['month']['vip_recharge'] = array_sum(array_column($month, 'vip_amount'));        //vip充值
            $data['month']['recharge_num'] = array_sum(array_column($month, 'recharge_suc_num')); //普通充值笔数
            $data['month']['un_recharge_num'] = array_sum(array_column($month, 'recharge_order_num')) - array_sum(array_column($month, 'recharge_suc_num')); //普通充值未支付笔数
            $data['month']['vip_num'] = array_sum(array_column($month, 'vip_suc_num'));           //开通vip数
            $data['month']['un_vip_num'] = array_sum(array_column($month, 'vip_order_num')) - array_sum(array_column($month, 'vip_suc_num'));           //开通vip未支付数
            $data['month']['recharge_rate'] = array_sum(array_column($month, 'recharge_order_num')) > 0 ? intval((array_sum(array_column($month, 'recharge_suc_num')) / array_sum(array_column($month, 'recharge_order_num'))) * 100) : 0;     //充值完成率
            $data['month']['vip_rate'] = array_sum(array_column($month, 'vip_order_num')) > 0 ? intval((array_sum(array_column($month, 'vip_suc_num')) / array_sum(array_column($month, 'vip_order_num'))) * 100) : 0;          //开通vip完成率
        }


        //总数据
        $total = OrderStat::find()
            ->asArray()
            ->all();

        if ($total) {
            $data['total']['normal_recharge'] = array_sum(array_column($total, 'recharge_amount'));  //普通充值
            $data['total']['vip_recharge'] = array_sum(array_column($total, 'vip_amount'));        //vip充值
            $data['total']['recharge_num'] = array_sum(array_column($total, 'recharge_suc_num')); //普通充值笔数
            $data['total']['un_recharge_num'] = array_sum(array_column($total, 'recharge_order_num')) - array_sum(array_column($total, 'recharge_suc_num')); //普通充值未支付笔数
            $data['total']['vip_num'] = array_sum(array_column($total, 'vip_suc_num'));           //开通vip数
            $data['total']['un_vip_num'] = array_sum(array_column($total, 'vip_order_num')) - array_sum(array_column($total, 'vip_suc_num'));           //开通vip未支付数
            $data['total']['recharge_rate'] = array_sum(array_column($total, 'recharge_order_num')) > 0
                ? intval((array_sum(array_column($total, 'recharge_suc_num')) / array_sum(array_column($total, 'recharge_order_num'))) * 100) : 0;     //充值完成率
            $data['total']['vip_rate'] = array_sum(array_column($total, 'vip_order_num')) > 0
                ? intval((array_sum(array_column($total, 'vip_suc_num')) / array_sum(array_column($total, 'vip_order_num'))) * 100) : 0;          //开通vip完成率
        }


        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'order' => $data
        ]);
    }
}
