<?php
namespace admin\controllers;


use Yii;

class ComicRankController extends BaseController
{
    public $name = '排行榜';

    public $modelClass = 'admin\models\comic\ComicRank';
    public $searchModelClass = 'admin\models\comic\ComicRankSearch';
}
