<?php
namespace admin\controllers\product;

class ComicRecommendController extends ProductBaseController
{
    public $name = '推荐位';

    public $modelClass = 'admin\models\comic\ComicRecommend';
    public $searchModelClass = 'admin\models\comic\ComicRecommendSearch';
}
