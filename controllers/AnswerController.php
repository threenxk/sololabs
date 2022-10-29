<?php
namespace admin\controllers;

/**
 * 问题帮助管理
 */
class AnswerController extends BaseController
{
    public $name = '问答';

    public $modelClass = 'admin\models\feedback\Answer';
    public $searchModelClass = 'admin\models\feedback\AnswerSearch';
}
