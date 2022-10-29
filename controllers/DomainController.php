<?php
namespace admin\controllers;

class DomainController extends BaseController
{
    public $name = '域名池';

    public $modelClass = 'admin\models\system\Domain';
    public $searchModelClass = 'admin\models\system\DomainSearch';
}
