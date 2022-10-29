<?php
namespace admin\controllers;

class RewardGiftController extends BaseController
{
    public $name = '礼物';

    public $modelClass = 'admin\models\ticket\RewardGift';
    public $searchModelClass = 'admin\models\ticket\RewardGiftSearch';
}
