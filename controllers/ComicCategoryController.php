<?php
namespace admin\controllers;

use admin\models\comic\ComicCategory;
use Yii;

class ComicCategoryController extends BaseController
{
    public $name = '漫画分类';

    public $modelClass = 'admin\models\comic\ComicCategory';
    public $searchModelClass = 'admin\models\comic\ComicCategorySearch';

}
