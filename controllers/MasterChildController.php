<?php
namespace admin\controllers;

use Yii;

/**
 * 分类管理
 */
class MasterChildController extends BaseController
{
    public $name = '用户好友关系';

    public $modelClass = 'admin\models\user\MasterChild';
    public $searchModelClass = 'admin\models\user\MasterChildSearch';

    public function actionButtons()
    {
        return [];
    }

}
