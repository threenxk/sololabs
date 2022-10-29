<?php
namespace admin\controllers;

use admin\models\pay\PayChannel;
use Yii;

/**
 * 支付通道管理
 */
class PayChannelController extends BaseController
{
    public $name = '支付通道';

    public $modelClass = 'admin\models\pay\PayChannel';
    public $searchModelClass = 'admin\models\pay\PayChannelSearch';


    public function init()
    {
        $pid = isset($_GET['pid']) ? $_GET['pid'] : 0;
        if ($pid && $channel = PayChannel::findOne($pid)) {
            $this->name = '【'.$channel->channel_name.'】子通道';

        }
        parent::init();
    }

    /**
     * index页操作按钮
     * @return array
     */
    public function actionButtons()
    {
        return [];
    }

}
