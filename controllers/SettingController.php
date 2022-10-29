<?php
namespace admin\controllers;

use admin\models\apps\AppsAlipay;
use admin\models\apps\AppsInfo;
use admin\models\apps\AppsPush;
use admin\models\apps\AppsShare;
use admin\models\apps\AppsWechatSetting;
use admin\models\pay\SubDiscount;
use admin\models\setting\SettingAppRule;
use admin\models\setting\SettingCommonRule;
use admin\models\setting\SettingDiscount;
use admin\models\setting\SettingInviteAward;
use admin\models\setting\SettingMessage;
use admin\models\setting\SettingMpInfo;
use admin\models\setting\SettingMpPay;
use admin\models\setting\SettingOss;
use admin\models\setting\SettingRemit;
use admin\models\setting\SettingServiceInfo;
use admin\models\setting\SettingSystem;
use admin\models\setting\SettingTicket;
use admin\models\setting\SettingWapPay;
use admin\models\setting\SettingWelfare;
use admin\models\setting\SettingWithdraw;
use admin\models\sign\SignAward;
use admin\models\ticket\TicketOption;
use common\helpers\OssHelper;
use common\helpers\OssUrlHelper;
use common\models\sign\SignExtraAward;
use common\helpers\Tool;
use common\models\setting\SettingSign;
use Yii;
use yii\web\Controller;
use common\helpers\RedisKey;
use yii\data\ActiveDataProvider;

/**
 * setting基础配置类 save时已经清理了缓存, 如无特殊情况不需要在 model下清理缓存了
 * Class SettingController
 * @package admin\controllers
 */
class SettingController extends Controller
{
    /**
     * 权限验证
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->id != 1) {  //不是总管理员
            echo  '此配置项只有总管理员可修改';
            return false;
        }
        return parent::beforeAction($action);
    }


    /**
     * 基础设置
     * @return string
     */
    public function actionSystem()
    {
        $model = SettingSystem::findOne(1);
        $group = 'system';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags' => Yii::$app->params['setting']['system'],
            'group' => $group
        ]);
    }

    /**
     * 客服信息
     */
    public function actionServiceInfo()
    {
        $model = SettingServiceInfo::findOne(1);
        $group = 'service-info';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['system'],
            'group' => $group
        ]);
    }

    /**
     * 短信服务
     */
    public function actionMessage()
    {
        $model = SettingMessage::findOne(1);
        $group = 'message';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['system'],
            'group' => $group,
        ]);
    }

    /**
     * 功能设置
     */
    public function actionBaseInfo()
    {
        $model = [
            'app_rule' => SettingAppRule::findOne(1),
            'system' => SettingSystem::findOne(1),
            'ticket' => SettingTicket::findOne(1),
            'apps_info' => AppsInfo::findOne(1),
            'sign' => SettingSign::findOne(1),
            'withdraw' => SettingWithdraw::findOne(1),
            'welfare' => SettingWelfare::findOne(1),
            'invite' => SettingInviteAward::find()->asArray()->all(),
            'sign_reward' => SignAward::find()->where(['product' => SignAward::PRODUCT_APP])->asArray()->all(),
            'sign_extra_award' => SignExtraAward::find()->where(['product' => SignExtraAward::PRODUCT_APP])->asArray()->all(),
            'sign_ticket_option' => TicketOption::find()->asArray()->all(),
            'apps_share' => AppsShare::findOne(1)->toArray()
        ];

        $this->_batchSave();
        return $this->render('update', [
            'model' => $model, // 重新find一次，给icon字段重新赋属性
            'tags' => Yii::$app->params['setting']['app'],
            'group' => 'base-info',
        ]);
    }

    /**
     * 更新功能设置
     */
    public function actionUpdateBaseInfo()
    {
        $params = Yii::$app->request->post();

        // 循环保存实例
        foreach ($params as $className => $v) {
            if (in_array($className, ['SettingInviteAward', 'TicketOption', 'SignExtraAward'])) { // 这些数据不适合用通用存储形式，跳过
                continue;
            }

            $nameSpace = 'admin\models\setting\\';
            if(substr($className, 0, 4) == 'Apps') {
                $nameSpace = 'admin\models\apps\\';
            }

            $modelClass =  $nameSpace . $className; // 类名
            $modelData = call_user_func([$modelClass, 'findOne'], 1); // 找到对应实例
            $this->_save($modelData);
        }

        //更新签到信息
        $signExtraAwardData = [];
        foreach ($params['SignExtraAward']['signId'] as $k => $v) {
            if($v != 0) {
                SignExtraAward::updateAll(['sign_days' => $params['SignExtraAward']['signIndex'][$k], 'num' => $params['SignExtraAward']['signValue'][$k]], ['id' => $v]);
                continue;
            }
            $signExtraAwardData[] = [$params['SignExtraAward']['signIndex'][$k], $params['SignExtraAward']['signValue'][$k], SignExtraAward::PRODUCT_APP];
        }
        Yii::$app->db->createCommand()->batchInsert(SignExtraAward::tableName(), ['sign_days', 'num', 'product'], $signExtraAwardData)->execute();


        //更新月票信息
        $ticketOptionData = [];
        foreach ($params['TicketOption']['ticketId'] as $k => $v) {
            if($v != 0) {
                TicketOption::updateAll(['title' => trim($params['TicketOption']['ticketIndex'][$k]), 'num' => $params['TicketOption']['ticketValue'][$k]], ['id' => $v]);
                continue;
            }
            $ticketOptionData[] = [$params['TicketOption']['ticketIndex'][$k], $params['TicketOption']['ticketValue'][$k]];
        }
        Yii::$app->db->createCommand()->batchInsert(TicketOption::tableName(), ['title', 'num'], $ticketOptionData)->execute();

        //更新邀请好友奖励
        foreach ($params['SettingInviteAward'] as $key => $value) {
            SettingInviteAward::updateAll(['read_time' => $value[0] * 60, 'num' => $value[1] * 100], ['id' => $key]);
        }

        return true;
    }

    /**
     * 更新金币值
     */
    public function actionUpdateSignCoin()
    {
        $params = Yii::$app->request->post();

        foreach ($params['coinArr'] as $k => $v) {
            SignAward::updateAll(['num' => $params['coin']], ['product' => SignAward::PRODUCT_APP, 'day' => $v]);
        }
    }

    /**
     * 删除月票选项
     */
    public function actionTicketDel()
    {
        $ticketOption = TicketOption::findOne(Yii::$app->request->post('id'));
        if ($ticketOption) {
            $ticketOption->delete();
        }
    }

    /**
     * 删除签到选项
     */
    public function actionSignDel()
    {
        $signExtraAward = SignExtraAward::findOne([
            'id' => Yii::$app->request->post('id'),
            'product' => SignExtraAward::PRODUCT_APP
        ]);
        if ($signExtraAward) {
            $signExtraAward->delete();
        }
    }


    /**
     * 开关设置
     */
    public function actionUpdateSwitch()
    {
        $data = Yii::$app->request->post();

        $modelClass = 'admin\models\setting\Setting' . ucfirst($data['className']); // 类名
        $modelData = call_user_func([$modelClass, 'findOne'], 1); // 找到对应实例
        $filed = $data['filed'];
        $modelData->$filed = $data['status']; // 修改状态

        $modelData->save(false);

        // 清理缓存
        Tool::clearCache(RedisKey::getSettingKey($data['className']));
    }

    /**
     * 三方登录设置
     */
    public function actionLoginShare()
    {
        $model = AppsWechatSetting::findOne(1);
        $group = 'login-share';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['app'],
            'group' => $group,
        ]);
    }

    /**
     * 三方支付设置
     */
    public function actionThreePay()
    {
        $model = [
            'ali_pay'       => AppsAlipay::findOne(1), // 支付宝付款
            'wechat_pay'    => AppsWechatSetting::findOne(1),
            'remit'         => SettingRemit::findOne(1) // 打款
        ];

        $this->_batchSave();
        return $this->render('update', [
            'model' => $model, // 重新find一次，给icon字段重新赋属性
            'tags'  => Yii::$app->params['setting']['app'],
            'group' => 'three-pay',
        ]);
    }


    /**
     * 三方推送设置
     */
    public function actionAppPush()
    {
        $model = AppsPush::findOne(1);
        $group = 'app-push';

        $this->_save($model, $model->app_id);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['app'],
            'group' => $group,
        ]);
    }


    /**
     * 阿里云oss配置
     * @return string
     */
    public function actionOss()
    {
        $model = SettingOss::findOne(1);
        $group = 'oss';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['system'],
            'group' => $group,
        ]);
    }

    /**
     * 微信基础信息
     */
    public function actionMpInfo()
    {
        $model = SettingMpInfo::findOne(1);
        $group = 'mp-info';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['mp'],
            'group' => $group,
        ]);
    }

    /**
     * 公众号支付设置
     */
    public function actionMpPay()
    {
        $model = SettingMpPay::findOne(1);
        $group = 'mp-pay';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['mp'],
            'group' => $group,
        ]);
    }

    /**
     * 公众号签到
     */
    public function actionMpSign()
    {
        $ruleModel = SettingCommonRule::findOne(1);
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('SignGold');
            $day  = $data['day'];
            $gold = $data['award'];
            foreach ($day as $k => $v) {
                if (!$gold[$k]) { // 没有设置价格跳过
                    continue;
                }

                SignAward::updateAll(['num' => $gold[$k] ?: 0], ['day' => $v, 'product' => SignAward::PRODUCT_MP]);
            }
            $ruleInfo = Yii::$app->request->post('SettingCommonRule');
            $ruleModel->sign_tips = $ruleInfo['sign_tips'];
            $ruleModel->save();
            Yii::$app->session->setFlash('updated', 1);
            return $this->redirect('mp-sign');
        }

        $model = SignAward::find()->select('day, num')->where(['product' => SignAward::PRODUCT_MP])->all();

        return $this->render('update-sign', [
            'model'     => $model,
            'ruleModel' => $ruleModel,
            'tags'      => YIi::$app->params['setting']['mp']
        ]);

    }

    /**
     * wap站支付配置
     * @return string
     */
    public function actionWapPay()
    {
        $model = SettingWapPay::findOne(1);
        $group = 'wap-pay';
        $this->_save($model);
        return $this->render('update', [
            'model' => $model,
            'tags'  => Yii::$app->params['setting']['mp'],
            'group' => $group,
        ]);
    }

    /**
     * 批量购买折扣
     * @return string|\yii\web\Response
     */
    public function actionSubDiscount() {
        if (Yii::$app->user->id != 1) {  //不是总管理员
            echo  '此配置项只有总管理员可修改';
            exit;
        }
        $type = Yii::$app->request->get('type', SubDiscount::CONTENT_TYPE_NOVEL);

        $setting = SettingDiscount::findOne(1);

        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('discount')['id'];
            $discountInput = Yii::$app->request->post('SubDiscount')['discount'];

            foreach ($ids as $k => $v) {
                SubDiscount::updateAll(['discount' => $discountInput[$k]], ['id' => $v]);
            }

            $this->_save($setting);

            return $this->redirect(['sub-discount', 'type' => $type]);
        }

        $models = SubDiscount::find()
            ->where(['content_type' => $type])
            ->all();

        return $this->render('update_discount', [
            'models' => $models,
            'tags' => SubDiscount::$contentTypeTexts,
            'type' => $type,
            'setting' => $setting,
        ]);

    }

    /**
     * 更新model
     * @param object $model
     * @param int    $appId appid
     */
    private function _save($model, $appId = 0)
    {
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            if (!$model->save()) {
                Yii::warning($model->errors);
            }

            Yii::$app->session->setFlash('updated', 1);
            // 清理缓存
            if (!$appId) { // 参数为空表示是系统配置
                Tool::batchClearCache(sprintf(RedisKey::CACHE_SETTING_PREFIX, ''));
            } else { // APP配置，清理APP缓存
                Tool::batchClearCache(sprintf(RedisKey::APPS_SETTING, ''));
            }
        }
    }

    /**
     * 批量保存model
     */
    private function _batchSave()
    {
        if (!Yii::$app->request->isPost) { // 不是post提交，直接返回
            return;
        }

        $data = Yii::$app->request->post(); // 提交的数据
        foreach ($data as $name => $vale) {
            if (substr($name, 0, 7) == 'Setting') { // 如果是设置的model
                $modelClass = 'admin\models\setting\\' . $name;
            } else { // apps类型的model
                $modelClass = 'admin\models\apps\\' . $name;
            }

            $model = $modelClass::findOne(1);
            $model->load($data);

            if (!$model->save()) {
                Yii::warning($model->errors);
            }
        }

        // 清理缓存
        Tool::batchClearCache(sprintf(RedisKey::CACHE_SETTING_PREFIX, ''));
        Tool::batchClearCache(sprintf(RedisKey::APPS_SETTING, ''));
    }
}
