<?php
namespace admin\controllers\product;

class AudioBannerController extends ProductBaseController
{
    public $name = 'Banner图';
    public $modelClass = 'admin\models\audio\AudioBanner';
    public $searchModelClass = 'admin\models\audio\AudioBannerSearch';
}
