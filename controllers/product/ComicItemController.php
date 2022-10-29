<?php
namespace admin\controllers\product;

class ComicItemController extends ProductBaseController
{
    public $name = '发现页推荐';

    public $modelClass = 'admin\models\comic\ComicItem';
    public $searchModelClass = 'admin\models\comic\ComicItemSearch';
}
