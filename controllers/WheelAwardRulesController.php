<?php
namespace admin\controllers;

use admin\models\setting\SettingWelfare;
use \Yii;

/**
 * 转盘活动
 */
class WheelAwardRulesController extends BaseController
{
    public $name = '规则说明';
    public $modelClass = 'admin\models\setting\SettingWelfare';

    /**
     * @inheritdoc
     */
    public function actionRules()
    {
        $rules = SettingWelfare::findOne(1)->toArray();
        return $this->render('rules', [
            'rules' => $rules
        ]);
    }

    /**
     * 保存大转盘规则
     * @return string
     */
    public function actionUpdateRules()
    {
        $param = \Yii::$app->request->post();
        $model = SettingWelfare::findOne(1);
        $model->load($param);
        if (!$model->save()) {
            Yii::warning($model->errors);
        }
    }

}
