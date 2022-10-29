<?php
namespace admin\controllers\product;

class BookBannerController extends ProductBaseController
{
    public $name = 'banner图';

    public $modelClass = 'admin\models\book\BookBanner';
    public $searchModelClass = 'admin\models\book\BookBannerSearch';
}
