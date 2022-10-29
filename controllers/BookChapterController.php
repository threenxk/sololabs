<?php

namespace admin\controllers;

use admin\models\book\Book;
use admin\models\book\BookChapter;
use admin\models\book\BookChapterSearch;
use common\helpers\Tool;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * BookChapterController implements the CRUD actions for BookChapter model.
 */
class BookChapterController extends Controller
{


    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!($bookId = Yii::$app->request->get('book_id')) || !($book = Book::findOne($bookId))) {
            throw new NotFoundHttpException;
        }

        Yii::$app->set('book', $book);
        return true;
    }

    /**
     * Lists all BookChapter models.
     * @return mixed
     */
    public function actionIndex($book_id)
    {
        $searchModel = new BookChapterSearch();
        $searchModel->book_id = $book_id;
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new BookChapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($book_id)
    {

        $model = new BookChapter();
        $model->book_id = $book_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'book_id' => $book_id]);
        }

        //新建章节 填充序号
        $display_order = BookChapter::find()->select('display_order')->where(['book_id' => $book_id, 'deleted_at' => 0])->orderBy('display_order desc')->scalar();
        $model->display_order = $display_order + 1;

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing BookChapter model.
     * @param int $book_id 作品id
     * @param integer $id 章节id
     * @param int $page
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($book_id, $id , $page=1)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'book_id' => $book_id, 'page' => $page]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing BookChapter model.
     * @param int $book_id
     * @param integer $id
     * @param int $page
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($book_id, $id, $page=1)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'book_id' => $book_id, 'page' => $page]);
    }

    /**
     * Finds the BookChapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BookChapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BookChapter::findOne($id)) !== null) {
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
                $result = BookChapter::deleteAll(['id' => $ids]);
                break;
        }

        exit($result ? '1' : '0');
    }
}
