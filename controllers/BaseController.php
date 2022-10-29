<?php
namespace admin\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use xiang\crud\Controller;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * @var string 名称
     */
    public $name;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        // 启用/禁用
        $actions['active'] = [
            'class' => ActiveAction::className(),
            'modelClass' => $this->modelClass,
        ];

        // 模板
        $actions['index']['view']  = '@admin/views/base/index';
        $actions['create']['view'] = '@admin/views/base/create';
        $actions['update']['view'] = '@admin/views/base/update';

        unset($actions['view']); // 默认移除view
        
        return $actions;
    }

    /**
     * 获取模板基础路径
     * @return string
     */
    public function getViewBasePath()
    {
        if (Yii::$app->controller->module instanceof \yii\base\Application) {
            return '@admin/views/' . $this->id;
        } else {
            return '@admin/modules/' . Yii::$app->controller->module->id . '/views/' . $this->id;
        }
    }

    /**
     * 页面标题
     * @return array
     */
    public function pageTitles()
    {
        return [
            'index'  => "{$this->name}列表",
            'view'   => "查看{$this->name}详情",
            'create' => "新增{$this->name}",
            'update' => "编辑{$this->name}",
        ];
    }

    /**
     * 面包屑导航条
     * @return array
     */
    public function breadcrumbs()
    {
        $breadcrumbs = [];

        foreach (['index', 'view', 'create', 'update'] as $actionID) {
            if ($actionID == 'index') { // 首页只需要第一个面包屑
                $breadcrumbs[$actionID][] = $this->name . '列表';
                continue;
            } else {
                $breadcrumbs[$actionID][] = ['label' => $this->name . '列表', 'url' => ['index']];
            }
            $breadcrumbs[$actionID][] = $this->getPageTitle($actionID);
        } 

        return $breadcrumbs;
    }

    /**
     * index页操作按钮
     * @return array
     */
    public function actionButtons()
    {
        return [
            [
                'label'   => $this->getPageTitle('create'),
                'url'     => ['create'],
                'options' => ['class' => 'btn green'],
            ],
        ];
    }

    /**
     * update/create页操作按钮
     * @return array
     */
    public function actionEditButtons() {
        return [
        ];
    }

    /**
     * 获取页面标题
     * @param $actionID
     * @return string
     */
    public function getPageTitle($actionID = null)
    {
        if (is_null($actionID)) {
            $actionID = Yii::$app->controller->action->id;
        }

        return ArrayHelper::getValue($this->pageTitles(), $actionID);
    }

    /**
     * 获取面包屑导航条
     * @param $actionID
     * @return array
     */
    public function getBreadcrumbs($actionID = null)
    {
        if (is_null($actionID)) {
            $actionID = Yii::$app->controller->action->id;
        }

        return ArrayHelper::getValue($this->breadcrumbs(), $actionID);
    }

    /**
     * 返回上一页
     * @return \yii\web\Response
     */
    public function back()
    {
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 批量删除
     * @return string
     */
    public function actionBatchDel()
    {
        $ids    = Yii::$app->request->post('ids');

        $model = Yii::$app->controller->modelClass;

        $result = $model::deleteAll([$model::getTableSchema()->primaryKey[0] => $ids]);

        exit($result===false ? '0' : '1');
    }

    /**
     * 置顶
     */
    public function actionUp()
    {
        $id = Yii::$app->request->get('id');

        $searchModel = $this->searchModelClass::find();
        foreach ($searchModel->each() as $model) {
            if ($model->id == $id) {
                $model->display_order = 255;
            } else {
                $model->display_order > 0 ? $model->display_order -=1 : $model->display_order;
            }
            $model->save(false);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 设置位置
     */
    public function actionSetPosition()
    {
        $id = Yii::$app->request->get('id');
        $order = intval(Yii::$app->request->get('order')); // 序号

        if (!$order) { // 异常序号
            return $this->redirect(Yii::$app->request->referrer);
        }

        // 找到自己这条记录
        $selfModel = $this->modelClass::findOne($id);
        if (!$selfModel) { // 异常数据返回
            return $this->redirect(Yii::$app->request->referrer);
        }

        $selfModel->setPosition($order);
    }
}
