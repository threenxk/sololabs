<?php
namespace admin\controllers\product;

use admin\models\advert\Advert;
use admin\models\advert\AdvertPosition;
use admin\models\setting\SettingAppRule;
use common\helpers\OssUrlHelper;
use common\helpers\RedisKey;
use common\helpers\Tool;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class AdvertController extends ProductBaseController
{
    public $modelClass = 'admin\models\advert\Advert';
    public $searchModelClass = 'admin\models\advert\AdvertSearch';

    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        $positionId = Yii::$app->request->get('position_id');

        $button = [
            [
                'label'   => '新增广告',
                'url'     => ['create', 'position_id' => $positionId],
                'options' => ['class' => 'btn green'],
            ]
        ];

        $positionInfo = AdvertPosition::findOne($positionId);
        if ($positionInfo->position == AdvertPosition::POSITION_CHAPTER_VIDEO) { // 视频广告
            $button[] = [
                'label'   => '配置免广告时长',
                'url'     => ['#'],
                'options' => ['class' => 'btn blue', 'data-toggle' => 'modal', 'data-target' => '#infoModal', 'onclick' => 'return false'],
            ];
        }

        return $button;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        // 去掉新增和编辑
        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    /**
     * 新增广告
     */
    public function actionCreate()
    {
        $model = new Advert();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($ret = $this->_verify($model)) { // 验证发现有错误
                $model->addError($ret[0], $ret[1]);
                return $this->render('@admin/views/product/' . $this->id . '/create', [
                    'model' => $model
                ]);
            }

            if (!$model->channelType) { // 没有频道类型，默认重置为小说
                $model->channelType = [TYPE_BOOK];
            }
            // 广告类型为频道信息里面的第一个数据
            $model->type = array_shift($model->channelType);

            // 如果是文件上传
            if ($model->ad_type == Advert::AD_TYPE_WEB) {
                // 获取上传的文件
                $file = UploadedFile::getInstance($model, 'image');
                if (!$file) {
                    $model->addError('image', '请上传一张图片文件');
                    return $this->render('@admin/views/product/' . $this->id . '/create', [
                        'model' => $model
                    ]);
                }

                // 图片宽高
                $size = getimagesize($file->tempName);
                $model->width = intval($size[0]);
                $model->height = intval($size[1]);
            } else {
                $model->detachBehavior('upload');
            }

            if (!$model->save()) {
                return $this->render('@admin/views/product/' . $this->id . '/create', [
                    'model' => $model
                ]);
            }

            // 频道信息还有其他的值，表示还有其他位置广告需要创建
            if ($model->channelType) {
                $data = $model->toArray();
                // 循环类型创建数据
                foreach ($model->channelType as $type) {
                    $dataModel = new Advert();
                    $dataModel->detachBehavior('upload');
                    $dataModel->load(['Advert' => $data]);
                    $dataModel->type = $type;
                    $dataModel->image = ArrayHelper::getValue($data, 'image', ''); // image字段不在rules里面，需要手动赋值
                    if (!$dataModel->save()) {
                        Yii::warning($dataModel->errors);
                    }
                }
            }

            return $this->redirect(['index', 'position_id' => $model['position_id']]);
        }

        return $this->render('@admin/views/product/' . $this->id . '/create', [
            'model' => $model
        ]);
    }

    /**
     * model 验证
     * @param $model
     * @return bool
     */
    private function _verify($model)
    {
        if ($model->ad_type == Advert::AD_TYPE_WEB) { // web类型广告
            if (!$model->skip_url) {
                return ['skip_url', '跳转地址不能为空'];
            }
        } else { // 穿山甲
            if (!$model->ad_key) {
                return ['ad_key', '广告Key不能为空'];
            }

            if (!$model->ad_android_key) {
                return ['ad_android_key', '广告Key不能为空'];
            }
        }
        return false;
    }

    public function actionUpdate()
    {
        $id = Yii::$app->request->get('id');
        $model = Advert::findOne($id);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($ret = $this->_verify($model)) { // 验证发现有错误
                $model->addError($ret[0], $ret[1]);
                return $this->render('@admin/views/product/' . $this->id . '/create', [
                    'model' => $model
                ]);
            }

            // 如果是文件上传
            if ($model->ad_type == Advert::AD_TYPE_WEB) {
                // 获取上传的文件
                $file = UploadedFile::getInstance($model, 'image');
                if (!$file) { // 没有上传新的文件，有两种情况，1是没有上传文件，2是没有修改文件
                    if ($model->image instanceof OssUrlHelper) { // 没有修改，则把值重置为初始状态
                        $model->image = $model->image->getBaseUrl();
                    } else { // 没有文件，提示上传
                        $model->addError('image', '请上传一张图片文件');
                        return $this->render('@admin/views/product/' . $this->id . '/create', [
                            'model' => $model
                        ]);
                    }
                } else {
                    // 有新文件图片宽高
                    $size = getimagesize($file->tempName);
                    $model->width = intval($size[0]);
                    $model->height = intval($size[1]);
                }
            } else {
                $model->detachBehavior('upload');
            }

            if (!$model->save()) {
                return $this->render('@admin/views/product/' . $this->id . '/update', [
                    'model' => $model
                ]);
            }

            return $this->redirect(['index', 'position_id' => $model['position_id']]);
        }

        return $this->render('@admin/views/product/' . $this->id . '/update', [
            'model' => $model,
        ]);
    }

    /**
     * 免广告时长设置
     */
    public function actionAdFreeSet()
    {
        $freeTime = Yii::$app->request->post('freeTime');
        $appRule = SettingAppRule::findOne(1);
        $appRule->ad_free_time = $freeTime;
        $appRule->save();

        Tool::batchClearCache(sprintf(RedisKey::CACHE_SETTING_PREFIX, ''));
    }
}
