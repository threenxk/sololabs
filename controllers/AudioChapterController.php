<?php
namespace admin\controllers;


use admin\models\audio\Audio;
use admin\models\audio\AudioChapter;
use admin\models\audio\AudioChapterSearch;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use Yii;

class AudioChapterController extends Controller
{

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!($audioId = Yii::$app->request->get('audio_id')) || !($audio= Audio::findOne($audioId))) {
            throw new NotFoundHttpException;
        }

        Yii::$app->set('audio', $audio);
        return true;
    }
    /**
     * Lists all BookChapter models.
     * @return mixed
     */
    public function actionIndex($audio_id)
    {
        $searchModel = new AudioChapterSearch();
        $searchModel->audio_id = $audio_id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($audio_id)
    {
        $model = new AudioChapter();
        $model->audio_id = $audio_id;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->upload_type == AudioChapter::UPLOAD_TYPE_URL) { // 链接上传，取消掉behavior，并且限制content必传
                $model->detachBehavior('upload');
                if (!$model->content) {
                    $model->addError('content', '章节内容不能为空');
                    return $this->render('create', ['model' => $model]);
                }
            }

            if ($model->save()) { // 保存成功后跳转章节列表
                return $this->redirect(['index', 'audio_id' => $audio_id]);
            } else {
                Yii::warning($model->errors);
            }
        }

        //新建章节 填充序号
        $display_order = AudioChapter::find()
            ->select('display_order')
            ->where(['audio_id' => $audio_id])
            ->orderBy('display_order desc')
            ->scalar();
        $model->display_order = $display_order ? $display_order + 1 : 1;

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    public function actionUpdate($audio_id, $id, $page=1)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'audio_id' => $audio_id, 'page' => $page]);
        }

        // 上一章
        $lastChapter = AudioChapter::find()
            ->select(['id'])
            ->andWhere(['audio_id' => $audio_id])
            ->andWhere(['<', 'display_order', $model->display_order])
            ->orderBy('display_order desc, id desc')
            ->one();
        // 下一章
        $nextChapter = AudioChapter::find()
            ->select(['id'])
            ->andWhere(['audio_id' => $audio_id])
            ->andWhere(['>', 'display_order', $model->display_order])
            ->orderBy(' display_order asc, id asc')
            ->one();

        return $this->render('update', [
            'model' => $model,
            'lastChapter' => $lastChapter,
            'nextChapter' => $nextChapter
        ]);
    }


    public function actionDelete($audio_id, $id, $page=1)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'audio_id' => $audio_id, 'page' => $page]);
    }

    protected function findModel($id)
    {
        if (($model = AudioChapter::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 批量操作
     */
    public function actionBatch()
    {
        $action = Yii::$app->request->get('action');
        $ids    = Yii::$app->request->post('ids');

        $result = false;

        switch ($action) {
            case 'batch_delete':
                // 批量删除
//                $result = Yii::$app->db->createCommand()->update(BookChapter::tableName(), ['deleted_at' => time()], ['id' => $ids])->execute();
                $result = AudioChapter::deleteAll(['id' => $ids]);
                break;
        }

        exit($result ? '1' : '0');
    }
}
