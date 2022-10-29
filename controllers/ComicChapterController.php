<?php
namespace admin\controllers;

use admin\models\comic\Comic;
use admin\models\comic\ComicChapter;
use admin\models\comic\ComicChapterPhotos;
use admin\models\comic\ComicChapterSearch;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Controller;


class ComicChapterController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!($comic_id = Yii::$app->request->get('comic_id')) || !($comic = Comic::findOne($comic_id))) {
            throw new NotFoundHttpException;
        }

        Yii::$app->set('comic', $comic);
        return true;
    }

    /**
     * Lists all ComicChapter models.
     * @param int $comic_id 漫画id
     * @return mixed
     */
    public function actionIndex($comic_id)
    {
        $searchModel = new ComicChapterSearch();
        $searchModel->comic_id = $comic_id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($comic_id)
    {
        $model = new ComicChapter();
        $model->comic_id = $comic_id;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if (!$model->images) {
                $model->addError('images', '章节图片必传');
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            $model->total_photos = count($model->images); // 章节图片数
            if (!$model->save()) { // 保存失败
                Yii::warning($model->errors);
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            if (!$model->images) { // 没有漫画图片
                return $this->redirect(['index', 'comic_id' => $comic_id]);
            }
            // 漫画章节图片入库
            $data = [];
            $fields = ['comic_id', 'comic_chapter_id', 'img', 'display_order', 'created_at', 'updated_at'];
            $time = time();
            foreach ($model->images as $key => $image) {
                $data[] = [$comic_id, $model->id, trim($image), $key, $time, $time];
            }
            Yii::$app->db->createCommand()->batchInsert(ComicChapterPhotos::tableName(), $fields, $data)->execute();
            return $this->redirect(['index', 'comic_id' => $comic_id]);
        }

        //新建章节 填充序号
        $display_order = ComicChapter::find()
            ->select('display_order')
            ->where(['comic_id' => $comic_id])
            ->orderBy('display_order desc')
            ->scalar();
        $model->display_order = $display_order ? $display_order + 1 : 1;

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param $comic_id
     * @param $id
     * @param $page
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($comic_id, $id, $page=1)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) { // 如果post 提交数据
            $model->load(Yii::$app->request->post());
            if (!$model->images) {
                $model->addError('images', '章节图片必传');
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

            $model->total_photos = count($model->images); // 章节图片数
            if (!$model->save()) {
                Yii::warning($model->errors);
            }

            // 更新章节图片信息
            $images = $model->images;
            if ($images) { // 有图片信息才处理
                $imagesId = Yii::$app->request->post('ComicChapter')['id']; // 提交上来的图片的id，新图id为0
                // 删除已经被移除的图片，章节下没有被提交上来的链接，先查出来已有的图片id，和提交的想比较，差集为需要删除的图片
                $photosId = ComicChapterPhotos::find()
                    ->select('id')
                    ->where(['comic_id' => $comic_id, 'comic_chapter_id' => $id])
                    ->column();
                $diffIds = array_diff($photosId, $imagesId);
                ComicChapterPhotos::deleteAll(['id' => $diffIds]);

                foreach ($images as $key => $image) {
                    if ($imagesId[$key]) { // 章节id没有变化，表示图片没有更改，跳过
                        continue;
                    }

                    $chapterPhoto = new ComicChapterPhotos();
                    $chapterPhoto->img = trim($image);
                    $chapterPhoto->comic_id = $comic_id;
                    $chapterPhoto->comic_chapter_id = $id;
                    $chapterPhoto->display_order = $key;
                    if (!$chapterPhoto->save()) {
                        Yii::warning($chapterPhoto->errors);
                    }
                }

                return $this->redirect(['index', 'comic_id' => $comic_id, 'page' => $page]);
            }
        }

        // 获取漫画章节图
        $model->images = $model->imgList;

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete($comic_id, $id, $page=1)
    {
        $this->findModel($id)->delete();

        // 删除图片
        ComicChapterPhotos::deleteAll(['comic_id' => $comic_id, 'comic_chapter_id' => $id]);

        return $this->redirect(['index', 'comic_id' => $comic_id, 'page' => $page]);
    }

    /**
     * Finds the ComicChapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ComicChapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ComicChapter::findOne($id)) !== null) {
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
        $comicId= Yii::$app->request->get('comic_id');

        $result = false;
        switch ($action) {
            case 'delete':
                ComicChapter::deleteAll(['id' => $ids]);
                ComicChapterPhotos::deleteAll(['comic_id' => $comicId, 'comic_chapter_id' => $ids]);
                break;
        }

        exit($result ? '1' : '0');
    }
}
