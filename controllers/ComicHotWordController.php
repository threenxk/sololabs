<?php
namespace admin\controllers;

use Yii;

class ComicHotWordController extends BaseController
{
    public $name = '热搜';

    public $modelClass = 'admin\models\comic\ComicHotWord';
    public $searchModelClass = 'admin\models\comic\ComicHotWordSearch';
}
