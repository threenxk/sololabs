<?php
namespace admin\controllers;

use admin\models\book\BookFreeList;
use admin\models\book\BookFreeTime;
use common\helpers\Tool;

use Yii;

/**
 * 限时免费管理
 */
class BookLimitedController extends BaseController
{
    public $modelClass = 'admin\models\book\BookFreeList';
    public $searchModelClass = 'admin\models\book\search\BookFreeListSearch';

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
            'update' => '编辑限免',
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 置顶
     */
    public function actionUp()
    {
        $id = Yii::$app->request->get('id');

        $freeBook = BookFreeList::find();
        foreach ($freeBook->each() as $book) {
            if ($book->id == $id) {
                $book->display_order = 255;
            } else {
                $book->display_order > 0 ? $book->display_order -=1 : $book->display_order;
            }
            $book->save();
        }

        $this->back();
    }

    /**
     * 设置时间
     */
    public function actionSet()
    {
        $model = BookFreeTime::find()->one();
        if (!$model) {
            $model = new BookFreeTime();
        }
        if ($model->load(Yii::$app->request->post())) {
            $model->save();

            Tool::clearApiCache('book');

            return $this->redirect('index');
        }

        return $this->render('set', [
            'model' => $model
        ]);
    }

}
