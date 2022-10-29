<?php
namespace admin\controllers;


use admin\models\book\BookCategory;
use Yii;

class BookCategoryController extends BaseController
{
    public $name = '分类';
    public $modelClass = 'admin\models\book\BookCategory';
    public $searchModelClass = 'admin\models\book\BookCategorySearch';

}
