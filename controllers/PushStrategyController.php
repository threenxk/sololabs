<?php
namespace admin\controllers;
use Yii;
use common\helpers\Tool;
use admin\models\push\PushStrategy;

class PushStrategyController extends BaseController
{
    public $name = '消息推送';

    public $modelClass = 'admin\models\push\PushStrategy';
    public $searchModelClass = 'admin\models\push\PushStrategySearch';

    public function actionButtons()
    {
        return [
            [
                'label'   => '辅助推送通道',
                'url'     => ['/push-passageway/index'],
                'options' => ['class' => 'btn blue'],
            ],
            [
                'label'   => $this->getPageTitle('create'),
                'url'     => ['create'],
                'options' => ['class' => 'btn green', 'id' => 'create-strategy'],
            ]
        ];
    }

    /**
     * 自定义新增推送
     * @return string
     */
    public function actionAdd()
    {
        return $this->render('add');
    }

    /**
     * 新增推送信息
     */
    public function actionAddInfo()
    {
        $model = new PushStrategy();

        if($model->load(['PushStrategy' => Yii::$app->request->post()])) {
            if (!$model->save()) {
                Yii::warning($model->errors);
            }
            return '1';
        }
        return '0';
    }

    /**
     * 更新推送信息
     */
     public function actionInfoUpdate()
     {
         $id = Yii::$app->request->post('id');
         $model = PushStrategy::findOne($id);
         if ($model !== null) {

             if ($model->load(['PushStrategy' => Yii::$app->request->post()])) {

                 if(!$model->save())
                 {
                     Yii::warning($model->errors);
                 }
                 return '1';
             }
         }
     }

    /**
     * 检验配置
     */
    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $model = PushStrategy::findOne($id);

        $osTypes = array_filter(explode(',', $model->os_type));
        $osTypeStr = '';
        if (count($osTypes) == 3) {
            $osTypeStr = '全部';
        } else {
            foreach ($osTypes as $item) {
                $osTypeStr .= PushStrategy::$osTypeMapX[$item].'/';
            }
        }
        $data['os_type'] = trim($osTypeStr, '/');

        $data['push_type'] = PushStrategy::$pushTypes[$model->push_type] . '推送';
        if ($model->status == PushStrategy::STATUS_UNFINISHED) {
            $data['push_result'] = '未发送';
        } else {
            $data['push_result'] = '发送成功 '.$model->push_suc;
        }
        $data['title'] = $model->title;
        $data['push_content'] = $model->push_content;

        $rang = '';
        switch ($model->user_type) {
            case PushStrategy::USER_TYPE_ALL:
                $rang .= '全部用户';
                break;
            case PushStrategy::USER_TYPE_GIVEN:
                if ($model->user_vip == PushStrategy::IS_VIP) {
                    $rang .= '会员';
                } else if ($model->user_type == PushStrategy::NO_VIP) {
                    $rang .= '普通用户';
                }

                if ($model->user_gender == PushStrategy::USER_GENDER_MALE) {
                    $rang .= '/'. '男';
                } else if ($model->user_gender == PushStrategy::USER_GENDER_FEMALE) {
                    $rang .= '/'. '女';
                }
                break;
            case PushStrategy::USER_TYPE_SET:
                $rang = 'UID['.$model->user_range.']';
                break;
        }

        $html = '<tr class="detail">
                    <td colspan="10">
                        <table style="width: 100%;">
                            <tr style="border: 0;">
                                <td style="text-align: left;padding-left:20px;width:20%">
                                    <p class="color">基本信息</p>
                                    <p>推送方式：<span class="color">'. $data['push_type'] .'</span></p>
                                    <p>推送状态：<span class="color">'. $data['push_result'] .'</span></p>
                                </td>
                                <td style="text-align: left;width:20%">
                                    <p>　　</p>
                                    <p>推送平台：<span class="color">'. $data['os_type'] .'</span></p>
                                    <p>推送对象：<span class="color">'. $rang .'</span></p>
                                </td>
                                <td style="text-align: left;">
                                    <p class="color">发送内容</p>
                                    <p>推送标题：<span class="color">'. $data['title'] .'</span></p>
                                    <p>推送内容：<span class="color">'. $data['push_content'] .'</span></p>
                                </td>
                        </table>
                    </td>
                </tr>';

        return Tool::responseJson(0, '', $html);
    }

    /**
     * 检验是否配置推送信息
     */
    public function actionCheck()
    {
        $appPush = Yii::$app->apps->get('push');
        if (!$appPush['access_key'] || !$appPush['access_secret']) {
            return Tool::responseJson(1, '阿里云access信息未配置,请先配置后再操作', ['url' => '/setting/app-push']);
        }

        return Tool::responseJson(0, '');
    }

    public function actionRepeat()
    {
        $id = Yii::$app->request->get('id');

        PushStrategy::updateAll(['status' => 0, 'push_suc' => 0, 'push_time' => 0], ['id' => $id]);
        return Tool::responseJson(0, '重发操作成功，稍后会为您重新推送此消息');
    }
}
