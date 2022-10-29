<?php

namespace admin\controllers\product;


class ComicBannerController extends ProductBaseController
{
    public $name = 'banner图';

    public $modelClass = 'admin\models\comic\ComicBanner';
    public $searchModelClass = 'admin\models\comic\ComicBannerSearch';
}
