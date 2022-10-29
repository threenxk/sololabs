<?php
namespace admin\controllers;

use admin\models\admin\Admin;
use admin\models\admin\AdminSearch;
use admin\models\admin\Role;
use admin\models\drp\AgentInfo;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class AdminController extends Controller
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->id != 1) { // 不是总管理员
            throw new ForbiddenHttpException('只有超级管理员才能操作此菜单');
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $searchModel = new AdminSearch();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
    }

    public function actionCreate()
    {
        $model = new Admin();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->setPassword($model->password); // 设置密码
            if ($model->save()) {
                return $this->redirect('index');
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = Admin::findOne($id);
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->password) { // 如果密码有更新
                $model->setPassword($model->password);
            }
            $model->save();
            return $this->redirect(['index']);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $admin = Admin::findOne($id);
        if ($admin) {
            // 管理员不可以删除
            if ($admin->id == 1) {
                return false;
            }

            // 渠道下面有代理不可以删除
            if ($admin->role_id == Role::ROLE_CHANNEL && (AgentInfo::findOne(['agent_admin_pid' => $admin->id]))) {
                Yii::$app->session->setFlash('un_delete', 1);
                return $this->redirect('index');
            }

            $admin->delete();
            // 删除渠道、代理相关信息
            $agentInfo = AgentInfo::findOne(['admin_id' => $admin->id]);
            if ($agentInfo) {
                $agentInfo->delete();
            }
        }

        return $this->redirect('index');
    }

    public function actionActive($id, $op)
    {
        $model = Admin::findOne($id);
        $op ? $model->enable() : $model->disable();

        return $this->redirect('index');
    }
}
