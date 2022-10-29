<?php

namespace admin\controllers;

/**
 * @property BaseController $controller
 * Class ActiveAction
 * @package admin\controllers
 */
class ActiveAction extends \xiang\crud\Action
{

    /**
     * 启用/禁用
     * @param $id
     * @param $op
     * @return mixed
     */
    public function run($id, $op)
    {
        $model = $this->findModel($id);

        $op ? $model->enable() : $model->disable();

        return $this->controller->back();
    }
}
