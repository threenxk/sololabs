<?php
namespace admin\controllers;

use admin\models\advert\StartPage;
use admin\models\book\Book;
use admin\models\book\BookBanner;
use admin\models\book\BookSetting;
use admin\models\book\BookUploadTask;
use Yii;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * 小说管理
 */
class BookController extends BaseController
{
    public $name = '小说';

    public $modelClass = 'admin\models\book\Book';
    public $searchModelClass = 'admin\models\book\BookSearch';

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
        $id = Yii::$app->request->get('book_id');
        $shelve = Yii::$app->request->get('shelve');

        //admin 下的book model绑定了图片上传
        $objBook = Book::find()->andWhere(['id' => $id])->one();

        if ($shelve == 0) { // 下线
            $objBook->online_status = Book::ONLINE_STATUS_OFF;
            // 下线关联信息
            BookBanner::updateAll(['status' => BookBanner::STATUS_DISABLED], ['action' => BookBanner::ACTION_BOOK, 'content' => $id]);
            StartPage::updateAll(['status' => BookBanner::STATUS_DISABLED], ['skip_type' => StartPage::SKIP_TYPE_BOOK, 'content' => $id]);
        } else {
            $objBook->online_status = Book::ONLINE_STATUS_ON;
        }
        $objBook->save(false);

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 上传作品
     * @return string
     */
    public function actionUpload()
    {
        $model = new BookUploadTask();
        $bookId = Yii::$app->request->get('book_id'); // 书籍id

        if ($model->load(Yii::$app->request->post()) ) {
            $BookUploadTask = Yii::$app->request->post();
            //作品上传操作
/*            $text = UploadedFile::getInstance($model, 'text');
            if (!$text) { // 文件必传
                $model->addError('text', '请上传一个txt文件');
                return $this->render('_upload', [
                    'model' => $model,
                ]);
            }

            if ($text->extension != 'txt') { // 文件格式限制
                $model->addError('text', '文件格式只能是txt');
                return $this->render('_upload', [
                    'model' => $model,
                ]);
            }

            $model->text = $text->name;*/

            $fileName = UPLOAD_FILE_DIR . '/' . $BookUploadTask['BookUploadTask']['text'];
/*            $fileName = UPLOAD_FILE_DIR . '/' . md5(time()).'.txt';
//            将临时放到指定目录
            move_uploaded_file($text->tempName, $fileName);*/

            $line = '';
            $fh = fopen($fileName, 'r');

            while(! feof($fh)) {
                $line = trim(fgets($fh));
                if (!empty($line)) {
                    break;
                }
            }

            fclose($fh);

            //判断文本字符集
            $encode = mb_detect_encoding($line, ["ASCII","UTF-8","GB2312","GBK","BIG5"]);
            if ($encode == 'UTF-8') {
                $charset = BookUploadTask::CHARSET_UTF8;
            } else {
                $charset = BookUploadTask::CHARSET_GBK;
            }
            // print_r($encode);exit;
            //记录章节分章任务
            $model->book_id = $bookId;
            $model->charset_type = $charset;
            $model->file = $fileName;

            if ($model->save()) { // 保存成功
                return $this->redirect(['/book-upload-task/index']);
            }
            Yii::warning($model->errors);
        }

        return $this->render('_upload', [
            'model' => $model,
        ]);
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
            case 'set_baoyue':
                // 批量设置会员
                $result = Yii::$app->db->createCommand()->update(Book::tableName(), ['is_baoyue' => 1], ['id' => $ids])->execute();
                break;

            case 'unset_baoyue':
                // 批量取消会员
                $result = Yii::$app->db->createCommand()->update(Book::tableName(), ['is_baoyue' => 0], ['id' => $ids])->execute();
                break;

            case 'shelve':
                // 批量上架
                $result = Yii::$app->db->createCommand()->update(Book::tableName(), ['online_status' => Book::ONLINE_STATUS_ON], ['id' => $ids])->execute();
                break;

            case 'unshelve':
                // 批量下架
                $result = Yii::$app->db->createCommand()->update(Book::tableName(), ['online_status' => Book::ONLINE_STATUS_OFF], ['id' => $ids])->execute();
                break;

            case 'delete':
                Book::deleteAll(['id' => $ids]);
                Book::batchDelete($ids);
                $result = 1;
        }
        Book::batchClearCache(); // 清理缓存

        exit($result===false ? '0' : '1');
    }

    /**
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view', ['model' => Book::findOne($id)]);
    }

    /**
     * 批量设置
     * @return string
     */
    public function actionBatchSetting() {

        $model = new BookSetting();

        if ($model->load(Yii::$app->request->post()) ) {
            if ($model->payType == Book::PAY_TYPE_ALL_FREE) { // 全免费
                $attributes['words_price'] = $attributes['free_chapters'] = $attributes['chapter_price'] = 0;
                $attributes['is_vip'] = Book::IS_VIP_NO;
            } else { // 付费
                $attributes['is_vip'] = Book::IS_VIP_YES;
                $attributes['free_chapters'] = $model->free_chapters;

                if ($model->priceType == Book::PRICE_TYPE_WORD) { // 千字定价
                    $attributes['words_price'] = $model->priceContent;
                    $attributes['chapter_price'] = 0;
                } else {
                    $attributes['chapter_price'] = $model->priceContent;
                    $attributes['words_price'] = 0;
                }

                if ($model->payType == Book::PAY_TYPE_ALL_COST) { // 全付费
                    $attributes['free_chapters'] = 0;
                }
            }

            $condition = 'deleted_at=0';
            switch ($model->book_range) {
                //免费
                case BookSetting::BOOK_RANGE_FREE:
                    $condition .= ' and words_price=0 and chapter_price=0';
                    break;
                //付费
                case BookSetting::BOOK_RANGE_FEE:
                    $condition .= ' and (words_price>0 or chapter_price>0)';
                    break;
            }

            Yii::$app->session->setFlash('updated', 1);
            BookSetting::updateAll($attributes, $condition);
            Book::batchClearCache(); // 清理缓存
            return $this->redirect('index');
        }

        return $this->render('batch_setting', ['model' => $model]);
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

        return Book::find()
            ->andFilterWhere(['like', 'name', $name])
            ->andWhere(['online_status' => Book::ONLINE_STATUS_ON])
            ->andFilterWhere(['channel_id' => $channelId, 'is_vip' => $isVip])
            ->orderBy(['id' => SORT_DESC])
            ->limit(20)
            ->all();

    }
}
