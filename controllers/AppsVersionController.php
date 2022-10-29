<?php
namespace admin\controllers;

use admin\models\apps\AppsMarketChannel;
use admin\models\apps\AppsPackageInfo;
use admin\models\apps\AppsVersionSearch;
use common\helpers\OssHelper;
use Yii;
use admin\models\apps\AppsVersion;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use common\helpers\Tool;

class AppsVersionController extends Controller
{
    /**
     * 版本首页
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AppsVersionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $packageInfo = ArrayHelper::index(AppsPackageInfo::find()->asArray()->all(), null, 'version_id');

        return $this->render('index', [
            'dataProvider'  => $dataProvider,
            'osType'        => Yii::$app->request->get('os_type', AppsVersion::OS_TYPE_IOS),
            'packageInfo'   => $packageInfo
        ]);
    }


    public function actionCreate()
    {
        $model = new AppsVersion();
        $osType = Yii::$app->request->get('os_type', AppsVersion::OS_TYPE_IOS);

        if (Yii::$app->request->isPost) {
            $appsVersion = Yii::$app->request->post('AppsVersion');
            // 版本信息
            $model->os_type = $appsVersion['os_type'];
            $model->ver_sn  = $appsVersion['ver_sn'];
            $model->content = $appsVersion['content'];
            $model->online_time = $appsVersion['online_time'];
            if (!$model->save()) {
                Yii::warning($model->errors);
                return $this->redirect(['index', 'os_type' => $osType]);
            }

            // 包渠道信息
            $channelInfo = Yii::$app->request->post('channel-info');
            $data = [];
            $time = time();
            foreach ($channelInfo as $channel) {
                $data[] = [
                    'app_id'        => 1,
                    'version_id'    => $model->id,
                    'channel_id'    => $channel['channel_id'],
                    'check_switch'  => AppsPackageInfo::SWITCH_OPEN,
                    'file_path'     => $channel['path'],
                    'created_at'    => $time,
                    'updated_at'    => $time,
                ];
            }
            Yii::$app->db->createCommand()->batchInsert(AppsPackageInfo::tableName(), ['app_id', 'version_id', 'channel_id', 'check_switch', 'file_path', 'created_at', 'updated_at'], $data)->execute();
            return $this->redirect(['index', 'os_type' => $model->os_type]);
        }

        // 此渠道下包信息
        $packageInfo = ArrayHelper::index(AppsPackageInfo::find()->where(['version_id' => $model->id])->asArray()->all(), 'channel_id');
        return $this->render('update', [
            'model'         => $model,
            'marketChannel' => AppsMarketChannel::find()->where(['os_type' => $osType])->all(), // 所有渠道信息
            'packageInfo'   => $packageInfo,
            'osType'         => $osType
        ]);
    }

    public function actionUpdate($id)
    {
        $model = AppsVersion::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $osType = $model->os_type;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            // 存储版本信息
            $model->load($post, 'AppsVersion');
            $model->save();

            // 存储包信息
            $channelInfo = Yii::$app->request->post('channel-info');
            $time = time();
            foreach ($channelInfo as $channel) {
                $package = AppsPackageInfo::findOne($channel['id']);
                $package->file_path = $channel['path'];
                $package->updated_at = $time;
                $package->save();
            }
            return $this->redirect(['index', 'os_type' => $model->os_type]);
        }

        // 此渠道下包信息
        $packageInfo = ArrayHelper::index(AppsPackageInfo::find()->where(['version_id' => $model->id])->asArray()->all(), 'channel_id');
        return $this->render('update', [
            'model'         => $model,
            'marketChannel' => AppsMarketChannel::find()->where(['os_type' => $osType])->all(), // 所有渠道信息,
            'packageInfo'   => $packageInfo,
            'osType'         => $osType
        ]);
    }

    /**
     * 强制更新开关
     * @return array
     */
    public function actionUpdateSwitch()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $type = Yii::$app->request->post('type');

        $appVersion = AppsVersion::findOne($id);
        if ($type == 1) {
            $appVersion->force_update = ($status == 'true') ? AppsVersion::FORCE_UPDATE_YES : AppsVersion::FORCE_UPDATE_NOT;
        } else {
            AppsVersion::updateAll(['is_release' => AppsVersion::RELEASE_OFF, 'force_update' => AppsVersion::FORCE_UPDATE_NOT], ['os_type' => $appVersion->os_type]);
            $appVersion->is_release = ($status == 'true') ? AppsVersion::RELEASE_ON : AppsVersion::RELEASE_OFF;
        }
        $appVersion->save();

        return Tool::responseJson(0, '操作成功!');
    }

    /**
     * 包过审开关
     */
    public function actionCheckSwitch()
    {
        $id = Yii::$app->request->post('id');

        $packageInfo = AppsPackageInfo::findOne($id);
        $packageInfo->toggle('check_switch');
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->get('id');
        $model = AppsVersion::findOne($id);
        if (!$model) { // 异常数据直接返回
            return $this->redirect(Yii::$app->request->referrer);
        }

        $model->delete();
        // 包也一并删除
        AppsPackageInfo::deleteAll(['version_id' => $id]);
        return $this->redirect(Yii::$app->request->referrer);
    }

}
