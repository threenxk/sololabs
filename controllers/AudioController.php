<?php
namespace admin\controllers;

use admin\models\advert\StartPage;
use admin\models\audio\Audio;
use admin\models\audio\AudioBanner;
use admin\models\audio\AudioChapter;
use admin\models\audio\AudioSetting;
use admin\models\audio\AudioUploadTask;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * 听书管理
 */
class AudioController extends BaseController
{
    public $name = '听书';

    public $modelClass = 'admin\models\audio\Audio';
    public $searchModelClass = 'admin\models\audio\AudioSearch';


    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['update']['redirect'] = ['update', 'id' => Yii::$app->request->get('id')];

        return $actions;
    }

    /**
     * 操作按钮
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
            [
                'label'   => '批量设置',
                'url'     => ['batch-setting'],
                'options' => ['class' => 'btn btn-warning btn-sm'],
            ],
        ];
    }

    /**
     * 上下架操作
     * @return \yii\web\Response
     */
    public function actionShelve() {
        $audioId = Yii::$app->request->get('id');
        $shelve = Yii::$app->request->get('shelve');

        $objAudio = Audio::find()->where(['id' => $audioId])->one();
        $objAudio->online_status = $shelve;
        $objAudio->save(false);
        if ($objAudio->online_status == Audio::ONLINE_STATUS_OFF) { // 下线关联数据
            StartPage::updateAll(['status' => StartPage::STATUS_DISABLED], ['content' => $audioId, 'skip_type' => StartPage::SKIP_TYPE_COMIC]);
            AudioBanner::updateAll(['status' => AudioBanner::STATUS_DISABLED], ['content' => $audioId, 'action' => AudioBanner::ACTION_AUDIO]);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 批量操作
     */
    public function actionBatch()
    {
        $action = Yii::$app->request->get('action');
        $ids = Yii::$app->request->post('ids');

        $result = false;

        switch ($action) {
            case 'set_baoyue':
                // 批量设置会员
                $result = Yii::$app->db->createCommand()->update(Audio::tableName(), ['is_baoyue' => 1], ['id' => $ids])->execute();
                break;

            case 'unset_baoyue':
                // 批量取消会员
                $result = Yii::$app->db->createCommand()->update(Audio::tableName(), ['is_baoyue' => 0], ['id' => $ids])->execute();
                break;

            case 'shelve':
                // 批量上架
                $result = Yii::$app->db->createCommand()->update(Audio::tableName(), ['online_status' => Audio::ONLINE_STATUS_ON], ['id' => $ids])->execute();
                break;

            case 'unshelve':
                // 批量下架
                $result = Yii::$app->db->createCommand()->update(Audio::tableName(), ['online_status' => Audio::ONLINE_STATUS_OFF], ['id' => $ids])->execute();
                break;
            case 'delete':
                Audio::deleteAll(['id' => $ids]);
                Audio::batchDelete($ids); // 批量删除
        }
        Audio::batchClearCache();

        exit($result===false ? '0' : '1');
    }


    /**
     * 搜索、用于推荐位等处
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $name = Yii::$app->request->get('name');
        $channelId = Yii::$app->request->get('channel_id'); // 频道
        $isVip = Yii::$app->request->get('is_vip'); // 会员


        return Audio::find()
            ->andFilterWhere(['like', 'name', $name])
            ->andWhere(['online_status' => Audio::ONLINE_STATUS_ON])
            ->andFilterWhere(['channel_id' => $channelId, 'is_vip' => $isVip])
            ->orderBy(['id' => SORT_DESC])
            ->limit(20)
            ->all();
    }

    /**
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view', ['model' => Audio::findOne($id)]);
    }

    /**
     * 上传作品
     * @return string
     */
    public function actionUpload()
    {
        set_time_limit(0);
        $model = new AudioUploadTask();

        $audioId = isset($_GET['audio_id']) ? $_GET['audio_id'] : 0;
        if (empty($audioId)) {
            return $this->redirect('/audio-upload');
        }

        if ($model->load(Yii::$app->request->post())) {
            //作品上传操作
            $file = UploadedFile::getInstance($model, 'upload_file');
            if (!$file) { // 文件必传
                $model->addError('upload_file', '请上传一个文件');
                return $this->render('_upload', [
                    'model' => $model,
                ]);
            }

            // 截取后缀 带.
            $extension = substr($file->name, strrpos($file->name, '.'));
            $fileName = ROOT_DIR.'/uploads/'.md5(time()) . $extension;
            //将临时放到指定目录
            move_uploaded_file($file->tempName, $fileName);

            //记录章节分章任务
            $model->audio_id = $audioId;
            $model->file     = $fileName;

            $model->save();

            $this->redirect(['/audio-chapter/index', 'audio_id' => $audioId,]);
        }

        return $this->render('_upload', [
            'model' => $model,
        ]);
    }


    /**
     * 批量设置
     * @return string
     */
    public function actionBatchSetting() {

        $model = new AudioSetting();

        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $attributes = [
                'free_chapters' => $model->free_chapters,
                'chapter_price' => $model->chapter_price
            ];

            if ($model->payType == Audio::PAY_TYPE_ALL_FREE) { // 全免费
                $attributes['free_chapters'] = $attributes['chapter_price'] = 0;
            } else if ($model->payType == Audio::PAY_TYPE_ALL_COST) { // 全付费
                $attributes['free_chapters'] = 0;
            }

            if ($attributes['chapter_price']) {
                $attributes['is_vip'] = Audio::IS_VIP_YES;
            } else {
                $attributes['is_vip'] = Audio::IS_VIP_NO;
            }

            $condition = 'deleted_at=0';
            switch ($model->audio_range) {
                //免费
                case AudioSetting::AUDIO_RANGE_FREE:
                    $condition .= ' and chapter_price=0 ';
                    break;
                //付费
                case AudioSetting::AUDIO_RANGE_FEE:
                    $condition .= ' and chapter_price>0 ';
                    break;
            }
            //设置弹窗
            Yii::$app->session->setFlash('updated', 1);
            AudioSetting::updateAll($attributes, $condition);
            Audio::batchClearCache(); // 清理缓存
            return $this->redirect('index');
        }

        return $this->render('batch_setting', ['model' => $model]);
    }
}
