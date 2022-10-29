<?php
namespace admin\controllers;

class RewardLogController extends BaseController
{
    public $name = '记录';

    public $modelClass = 'admin\models\ticket\RewardLog';
    public $searchModelClass = 'admin\models\ticket\RewardLogSearch';

    /**
     * index页操作按钮
     * @return array
     */
    public function actionButtons()
    {
        return [];
    }
}
