<?php
namespace admin\controllers;

use admin\models\book\BookRank;
use Yii;

class BookRankController extends BaseController
{
    public $name = '排行榜';
    public $modelClass = 'admin\models\book\BookRank';
    public $searchModelClass = 'admin\models\book\BookRankSearch';
}
