<?php
namespace admin\controllers;

use admin\models\book\BookFreeTime;
use Yii;

class BookFreeListController extends BaseController
{
    public $name = '限免小说';
    public $modelClass = 'admin\models\book\BookFreeList';
    public $searchModelClass = 'admin\models\book\BookFreeListSearch';


    public function actionButtons()
    {
        return [
            [
                'label'   => $this->getPageTitle('create'),
                'url'     => ['create'],
                'options' => ['class' => 'btn green'],
            ],
            [
                'label'   => '设置限免时间',
                'url'     => ['set'],
                'options' => ['class' => 'btn blue'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function pageTitles()
    {
        return [
            'index'  => '限免小说列表',
            'create' => '新增限免小说',
            'update' => '编辑限免小说',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'book_id' => '作品名',
            'display_order' => '显示顺序',
            'enable_time' => '生效时间',
            'expire_time' => '过期时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * 设置时间
     */
    public function actionSet()
    {
        $model = BookFreeTime::findOne(1);
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->save()) {
                return $this->render('set', [
                    'model' => $model
                ]);
            }
            return $this->redirect('index');
        }

        return $this->render('set', [
            'model' => $model
        ]);
    }
}
