<?php
namespace admin\controllers;

use admin\models\admin\LoginForm;
use admin\models\admin\Role;
use admin\models\user\UpdatePasswordForm;
use admin\models\user\UserSafe;
use common\helpers\Tool;
use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class'  => 'yii\web\ErrorAction',
                'layout' => false,
            ],

            'captcha' => [
                'class' => 'admin\components\CaptchaAction', // 自定义为数字
                'maxLength' => 4,
                'minLength' => 4,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $notice_flag = false; //活动提示文案
        // todo 修改为走redis,多机器会有问题
        if (Yii::$app->cache->get('login_flag')) {
            //如果是代理
            if (in_array(Yii::$app->user->identity->role_id, [Role::ROLE_CHANNEL, Role::ROLE_AGENT])) {
                $notice_flag = true;
            }
            Yii::$app->cache->delete('login_flag');
        }

        return $this->render('index', [
            'notice_flag' => $notice_flag
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->cache->set('login_flag', '1');
            return $this->goBack();
        } else {
            $model->password = '';
            $model->captcha = '';

            $this->layout = false;
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Update Password action.
     *
     * @return string
     */
    public function actionPassword()
    {
        $model = new UpdatePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->updatePassword()) {
            return $this->redirect(['site/logout']);
        }

        return $this->render('password', ['model' => $model]);
    }


    /**
     * @return string
     */
    public function actionSafeKey()
    {
        $userSafe = UserSafe::findOne(1);
        if (!$userSafe->auth_key) { // 首次进入没有key，需要生成
            $userSafe->auth_key = Tool::getRandKey();
            $userSafe->save();
        }

        if (Yii::$app->request->isPost) {
            $userSafe->load(Yii::$app->request->post());
            $userSafe->save();
        }

        return $this->render('safe-key', [
            'userSafe' => $userSafe
        ]);
    }

    /**
     * 重新生成免密登录
     */
    public function actionReAuth()
    {
        $userSafe = UserSafe::findOne(1);
        $userSafe->auth_key = Tool::getRandKey();
        $userSafe->save();
        return $this->redirect('/site/safe-key');
    }
}
