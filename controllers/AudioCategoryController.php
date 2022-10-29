<?php
namespace admin\controllers;

use admin\models\audio\AudioCategory;
use Yii;

/**
 * 分类管理
 */
class AudioCategoryController extends BaseController
{
    public $name = '分类';

    public $modelClass = 'admin\models\audio\AudioCategory';
    public $searchModelClass = 'admin\models\audio\AudioCategorySearch';

}
