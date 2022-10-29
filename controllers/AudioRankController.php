<?php
namespace admin\controllers;

use admin\models\audio\AudioRank;
use Yii;

class AudioRankController extends BaseController
{
    public $name = '排行榜';

    public $modelClass = 'admin\models\audio\AudioRank';
    public $searchModelClass = 'admin\models\audio\AudioRankSearch';
}
