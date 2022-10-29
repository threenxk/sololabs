<?php
namespace admin\controllers;

class TicketLogController extends BaseController
{
    public $name = '记录';

    public $modelClass = 'admin\models\ticket\TicketLog';
    public $searchModelClass = 'admin\models\ticket\TicketLogSearch';

    /**
     * index页操作按钮
     * @return array
     */
    public function actionButtons()
    {
        return [];
    }
}
