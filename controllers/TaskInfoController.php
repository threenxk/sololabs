<?php
namespace admin\controllers;

use admin\models\user\TaskInfo;
use Yii;

/**
 * 任务管理
 */
class TaskInfoController extends BaseController
{
    public $name = '任务';
    public $modelClass = 'admin\models\user\TaskInfo';
    public $searchModelClass = 'admin\models\user\TaskInfoSearch';
    
    public function actions()
    {
        $action = parent::actions();
        
        unset($action['create']);
        unset($action['update']);

        return $action;
    }

    public function actionButtons()
    {
        return [];
    }

    public function actionUpdate($id)
    {
        $model = TaskInfo::findOne($id);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            // 修改阅读时长
            if ($model->readTime) {
                $model->content = json_encode(['readTime' => $model->readTime * 60]);
            }
            $model->save();
            return $this->redirect('index');
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }
}
