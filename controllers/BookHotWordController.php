<?php
namespace admin\controllers;

use Yii;
use yii\web\Controller;

/**
 * 热词管理
 */
class BookHotWordController extends BaseController
{
    public $name = '热搜';
    public $modelClass = 'admin\models\book\BookHotWord';
    public $searchModelClass = 'admin\models\book\BookHotWordSearch';
}
