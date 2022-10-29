<?php
namespace admin\controllers\product;

class AudioRecommendController extends ProductBaseController
{
    public $name = '推荐位';
    public $modelClass = 'admin\models\audio\AudioRecommend';
    public $searchModelClass = 'admin\models\audio\AudioRecommendSearch';
}
