<?php
namespace admin\controllers;

use admin\models\audio\AudioHotWord;
use Yii;
class AudioHotWordController extends BaseController
{
    public $name = '热搜';

    public $modelClass = 'admin\models\audio\AudioHotWord';
    public $searchModelClass = 'admin\models\audio\AudioHotWordSearch';

}
