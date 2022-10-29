<?php
namespace admin\controllers;

use admin\models\pay\Expend;
use admin\models\data\ExtractLog;
use admin\models\user\UserAssets;
use common\helpers\Wxpay;
use Yii;
use common\helpers\Tool;

/**
 * 提现管理
 */
class ExtractLogController extends BaseController
{
    public $modelClass = 'admin\models\data\ExtractLog';
    public $searchModelClass = 'admin\models\data\search\ExtractLogSearch';

    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        return [];
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => ExtractLog::findOne($id)]);
    }

    // 提现审核
    public function actionHandle()
    {
        $id = Yii::$app->request->get('id');
        $status = Yii::$app->request->get('status');  //强制关注章节
        $remark = Yii::$app->request->get('remark');  //渠道名称
        $adminId = Yii::$app->user->identity->id; //管理员ID

        $t = Yii::$app->db->beginTransaction();
        try {
            $extractInfo = ExtractLog::findOne($id);
            $extractInfo->status = $status;
            if ($status == ExtractLog::STATUS_REJECT) {
                $extractInfo->remark = $remark;
            }
            $extractInfo->admin_id = $adminId;

            if ($status == 1) {
                // 开始体现，并减少用户金币，增加用户余额
                $wxPay = new Wxpay(Wxpay::TRADE_TYPE_APP);
                $wxExtract = $wxPay->goExtract($extractInfo->trade_no, $extractInfo->total_fee * 100, $extractInfo->openid, '金额提现', '');
                if ($wxExtract) {
                    $extractInfo->out_trade_no = $wxExtract['payment_no'];
                }
            } else {
                // 驳回增加用户金额
                $userAssets = UserAssets::findOne($extractInfo->uid);
                $userAssets->gold_remain = $userAssets->gold_remain + $extractInfo->gold_num;
                $userAssets->save();

                // 写入用户流水记录
                $objExpend = new Expend();
                $objExpend->expend_no = $extractInfo->trade_no;
                $objExpend->uid = $extractInfo->uid;
                $objExpend->type = Expend::TYPE_EXTRACT_NO;
                $objExpend->subject = Expend::$expend_subject[Expend::TYPE_EXTRACT_NO];
                $objExpend->amount = 1;
                $objExpend->price = $extractInfo->total_fee;
                $objExpend->total_price = $extractInfo->total_fee;
                $objExpend->gold_cost = $extractInfo->gold_num;
                $objExpend->silver_cost = 0;
                $objExpend->from_channel = 0;
                $objExpend->product = Expend::PRODUCT_APP;
                $objExpend->gold_remain = $userAssets->gold_remain;
                $objExpend->silver_remain = $userAssets->silver_remain;
                $objExpend->note = Expend::$expend_subject[Expend::TYPE_EXTRACT_NO];;
                $objExpend->ip = Tool::getIp();
                $objExpend->save();
            }

            $extractInfo->save();

            $t->commit();
            return Tool::responseJson(1, '操作成功！');
        } catch(\Exception $e) {
            $t->rollBack();
            return Tool::responseJson(1, '操作失败！');
        }
    }

}
