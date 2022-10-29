<?php
namespace admin\controllers;

/**
 * 推送通道管理
 */
class PushPassagewayController extends BaseController
{
    public $name = '辅助推送通道';

    public $modelClass = 'admin\models\push\PushPassageway';
    public $searchModelClass = 'admin\models\push\PushPassagewaySearch';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        return [];
    }

}
