<?php
namespace admin\controllers\product;

class BookRecommendController extends ProductBaseController
{
    public $name = '推荐位';

    public $modelClass = 'admin\models\book\BookRecommend';
    public $searchModelClass = 'admin\models\book\BookRecommendSearch';
}
