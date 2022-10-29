<?php
namespace admin\controllers;

/**
 * 转盘活动
 */
class WheelAwardPrizeController extends BaseController
{
    public $name = '转盘活动';

    public $modelClass = 'admin\models\welfare\WheelAwardPrize';
    public $searchModelClass = 'admin\models\welfare\WheelAwardPrizeSearch';

    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        return [];
    }

    // 移除新增和删除
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['delete']);
        return $actions;
    }
}
