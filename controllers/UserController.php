<?php
namespace admin\controllers;

use admin\models\audio\AudioComment;
use admin\models\book\BookComment;
use admin\models\comic\ComicComment;
use admin\models\drp\DrpUser;
use admin\models\user\CoinDetail;
use admin\models\user\User;
use admin\models\user\UserAssets;
use admin\models\user\UserAuthApp;
use admin\models\user\UserAuthMp;
use common\helpers\Tool;
use common\models\pay\Expend;
use common\models\user\UserVip;
use common\services\PayService;
use yii;

/**
 * 用户管理
 */
class UserController extends BaseController
{
    public $name = '用户';
    public $modelClass = 'admin\models\user\User';
    public $searchModelClass = 'admin\models\user\UserSearch';

    public function actions()
    {
        $actions = parent::actions();

        // 去掉active
        unset($actions['active']);

        return $actions;
    }

    /**
     * 开通会员
     */
    public function actionVipBuy()
    {
        $uid = Yii::$app->request->get('uid');
        $days = Yii::$app->request->get('days');
        if (!is_numeric($days) || strpos($days, '.') !== false) {
            return Tool::responseJson(1, '天数必须为整数');
        }
        $model = UserVip::findOne(['uid' => $uid]);
        if (!$model) {
            if ($days <= 0) { //如果天数小于等于0直接返回
                return Tool::responseJson(0, '');
            }
            $model = new UserVip();
            $model->uid = $uid;
        }

        //如果会员已经过期
        if ($model->end_time < time()) {
            $model->end_time = time();
        }
//        $model->continue_time = $days * 86400;
//        if ($model->continue_time < 0) {
//            $model->continue_time = 0;
//        }
        $model->start_time = $model->start_time ? $model->start_time : time();
        $model->end_time = ($model->end_time ? $model->end_time : time()) + 86400 * $days;
        if (!$model->save()) {
            Yii::warning($model->errors);
        }

        return Tool::responseJson(0, '');
    }

    /*
     * 赠送金币
     * */
    public function actionGold()
    {
        $uid = Yii::$app->request->get('uid');
        $money = Yii::$app->request->get('money');
        $type = Yii::$app->request->get('type');
        if (!is_numeric($money) || strpos($money, '.') !== false) {
            return Tool::responseJson(1, '货币数必须为整数');
        }

        $payService = new PayService();
        if ($money > 0) { //赠送金币
            if ($type == 1) { // 金币
                $result = $payService->interfacePay($uid, Expend::TYPE_SYSTEM, $money, '系统充值', $type);
            } else { // 硬币
                $result = $payService->interfaceCoin($uid, CoinDetail::TYPE_SYSTEM, $money, '系统充值');
            }

        } else { //扣除金币，注意传入值为负数
            $absMoney = abs($money);

            // 判断余额是否足够
            $userAsset = UserAssets::findOne(['uid' => $uid]);
            if (!$userAsset) {
                return Tool::responseJson(1, '余额不足');
            }

            if ($type == 1) { // 金币
                if (($userAsset->gold_remain + $userAsset->silver_remain) < $absMoney) {
                    return Tool::responseJson(1, '余额不足');
                }
                $result = $payService->interfacePay($uid, Expend::TYPE_SYSTEM_REDUCE, $absMoney, '系统扣除', $type);
            } else { // 硬币
                if ($userAsset->coin_remain < $absMoney) {
                    return Tool::responseJson(1, '余额不足');
                }
                $result = $payService->interfaceCoin($uid, CoinDetail::TYPE_SYSTEM_REDUCE, $absMoney, '系统充值');
            }
        }

        if ($result) {
            return Tool::responseJson(0, '');
        }

        return Tool::responseJson(1, '系统错误,稍后重试');
    }

    /**
     * 批量删除
     */
    public function actionBatchDel()
    {
        $ids = Yii::$app->request->post('ids');
        User::deleteAll(['uid' => $ids]);
        UserAuthApp::deleteAll(['uid' => $ids]);
        UserAuthMp::deleteAll(['uid' => $ids]);
        DrpUser::deleteAll(['uid' => $ids]);

        exit('1');
    }

    public function actionActive()
    {
        $id = Yii::$app->request->get('id');
        $op = Yii::$app->request->get('op');

        $user = User::findOne($id);
        if (!$user) { // 用户不存在直接返回
            return $this->redirect(Yii::$app->request->referrer);
        }

        if ($op) { // 上线
             $user->status = User::STATUS_ENABLED;
             $user->save(false);
             // 评论上线
            BookComment::updateAll(['status' => BookComment::STATUS_YES], ['uid' => $id, 'status' => BookComment::STATUS_FORBIDDEN]);
            ComicComment::updateAll(['status' => ComicComment::STATUS_YES], ['uid' => $id, 'status' => ComicComment::STATUS_FORBIDDEN]);
            AudioComment::updateAll(['status' => AudioComment::STATUS_YES], ['uid' => $id, 'status' => AudioComment::STATUS_FORBIDDEN]);
        } else { // 下线
            $user->status = User::STATUS_DISABLED;
            $user->user_token = Tool::getRandKey(); // 生成新的token，用于踢下线
            $user->save(false);
            // 评论下线
            BookComment::updateAll(['status' => BookComment::STATUS_FORBIDDEN], ['uid' => $id, 'status' => BookComment::STATUS_YES]);
            ComicComment::updateAll(['status' => ComicComment::STATUS_FORBIDDEN], ['uid' => $id, 'status' => ComicComment::STATUS_YES]);
            AudioComment::updateAll(['status' => AudioComment::STATUS_FORBIDDEN], ['uid' => $id, 'status' => AudioComment::STATUS_YES]);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}
