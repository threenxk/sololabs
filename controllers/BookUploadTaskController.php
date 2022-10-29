<?php
namespace admin\controllers;

use Yii;
class BookUploadTaskController extends BaseController
{
    public $name = '上传状态';

    public $modelClass = 'admin\models\book\BookUploadTask';
    public $searchModelClass = 'admin\models\book\BookUploadTaskSearch';


    public function actionButtons()
    {
        return [
            [
                'label'   => '小说列表',
                'url'     => ['/book/index'],
                'options' => ['class' => 'btn green'],
            ],
            [
                'label'   => '返回',
                'url' => ['/book-upload-task/index'],
                'options' => ['class' => 'btn default'],
            ]
        ];
    }
}
