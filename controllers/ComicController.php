<?php
namespace admin\controllers;

use admin\models\advert\StartPage;
use admin\models\comic\Comic;
use admin\models\comic\ComicBanner;
use admin\models\comic\ComicChapter;
use admin\models\comic\ComicChapterPhotos;
use admin\models\comic\ComicSetting;
use yii\web\Response;
use Yii;

/**
 * 漫画管理
 */
class ComicController extends BaseController
{
    public $name = '漫画';

    public $modelClass = 'admin\models\comic\Comic';
    public $searchModelClass = 'admin\models\comic\ComicSearch';

    public function actions()
    {
        $actions = parent::actions();

        $actions['update']['redirect'] = ['update', 'id' => Yii::$app->request->get('id')];

        return $actions;
    }


    public function actionView($id)
    {
        return $this->render('view', ['model' => Comic::findOne($id)]);
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
        $comicId = Yii::$app->request->get('comic_id');
        $shelve = Yii::$app->request->get('shelve');

        $objComic = Comic::find()->where(['comic_id' => $comicId])->one();
        $objComic->online_status = $shelve;

        $objComic->save(false);
        if ($objComic->online_status == Comic::ONLINE_STATUS_HIDE) { // 下线关联数据
            StartPage::updateAll(['status' => StartPage::STATUS_DISABLED], ['content' => $comicId, 'skip_type' => StartPage::SKIP_TYPE_COMIC]);
            ComicBanner::updateAll(['status' => ComicBanner::STATUS_DISABLED], ['content' => $comicId, 'action' => ComicBanner::ACTION_COMIC]);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionBatch()
    {
        $action = Yii::$app->request->get('action');
        $comicIds = Yii::$app->request->post('ids');

        $result = false;

        switch ($action) {
            case 'set_baoyue':
                // 批量设置会员
                $result = Yii::$app->db->createCommand()->update(Comic::tableName(), ['is_baoyue' => 1], ['comic_id' => $comicIds])->execute();
                break;

            case 'unset_baoyue':
                // 批量取消会员
                $result = Yii::$app->db->createCommand()->update(Comic::tableName(), ['is_baoyue' => 0], ['comic_id' => $comicIds])->execute();
                break;

            case 'shelve':
                // 批量上架
                $result = Yii::$app->db->createCommand()->update(Comic::tableName(), ['online_status' => Comic::ONLINE_STATUS_SHOW], ['comic_id' => $comicIds])->execute();
                break;

            case 'unshelve':
                // 批量下架
                $result = Yii::$app->db->createCommand()->update(Comic::tableName(), ['online_status' => Comic::ONLINE_STATUS_HIDE], ['comic_id' => $comicIds])->execute();
                break;
            case 'delete':
                Comic::deleteAll(['comic_id' => $comicIds]);
                Comic::batchDelete($comicIds); // 清理数据和缓存
                break;
        }
        Comic::batchClearCache();

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

        return Comic::find()
            ->andFilterWhere(['like', 'name', $name])
            ->andWhere(['online_status' => Comic::ONLINE_STATUS_SHOW])
            ->andFilterWhere(['channel_id' => $channelId, 'is_vip' => $isVip])
            ->orderBy(['comic_id' => SORT_DESC])
            ->limit(20)
            ->all();
    }



    /**
     * 批量设置
     * @return string
     */
    public function actionBatchSetting() {

        $model = new ComicSetting();

        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $attributes = [
                'free_chapters' => $model->free_chapters,
                'chapter_price' => $model->chapter_price
            ];

            if ($model->payType == Comic::PAY_TYPE_ALL_FREE) { // 全免费
                $attributes['free_chapters'] = $attributes['chapter_price'] = 0;
            } else if ($model->payType == Comic::PAY_TYPE_ALL_COST) { // 全付费
                $attributes['free_chapters'] = 0;
            }

            if ($attributes['chapter_price']) {
                $attributes['is_vip'] = Comic::IS_VIP_YES;
            } else {
                $attributes['is_vip'] = Comic::IS_VIP_NO;
            }

            $condition = 'deleted_at=0';
            switch ($model->comic_range) {
                //免费
                case ComicSetting::COMIC_RANGE_FREE:
                    $condition .= ' and chapter_price=0 ';
                    break;
                //付费
                case ComicSetting::COMIC_RANGE_FEE:
                    $condition .= ' and chapter_price>0 ';
                    break;
            }
            //设置弹窗
            Yii::$app->session->setFlash('updated', 1);
            ComicSetting::updateAll($attributes, $condition);
            Comic::batchClearCache(); // 清理缓存
            return $this->redirect('index');
        }

        return $this->render('batch_setting', ['model' => $model]);
    }

}
