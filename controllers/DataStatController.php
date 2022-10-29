<?php
namespace admin\controllers;

use admin\models\audio\Audio;
use admin\models\audio\AudioStat;
use admin\models\audio\AudioStatSearch;
use admin\models\book\Book;
use admin\models\book\BookStat;
use admin\models\book\BookStatSearch;
use admin\models\comic\Comic;
use admin\models\comic\ComicStat;
use admin\models\comic\ComicStatSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

/**
 * 内容统计
 */
class DataStatController extends Controller
{
    /**
     * 作品统计汇总
     * @return string
     */
    public function actionBook()
    {
        $keyword = isset(Yii::$app->request->queryParams['keyword']) ? Yii::$app->request->queryParams['keyword'] : '';
        $sort = isset(Yii::$app->request->queryParams['sort']) ? Yii::$app->request->queryParams['sort'] : 1;

        $tb = Book::tableName();
        $objBookStat = BookStat::find()
            ->joinWith('book')
            ->andFilterWhere(
                [
                    'OR',
                    ['like', $tb.'.name', $keyword],
                    ['like', $tb.'.author', $keyword],
                    ['like', $tb.'.source', $keyword],
                ]
            )
            ->andWhere(['>', 'date', date('Ymd', strtotime("-90 day"))])
            ->all();

        $data = [];
        /** @var BookStat $objStat */
        foreach ($objBookStat as $objStat) {
            if (!$objStat->book) {
                continue;
            }
            if (!isset($data[$objStat->book_id])) {
                $data[$objStat->book_id] = [
                    'book' => $objStat->book,
                    'book_id' => $objStat->book_id,
                    'name' => $objStat->book ? $objStat->book->name : '',
                    'views' => 0,
                    'favors' => 0,
                    'recommend' => 0,
                    'expend_paid' => 0,
                    'expend_user' => 0,
                    'expend_num' => 0,
                    'pay_income' => 0,
                    'pay_user' => 0,
                    'pay_num' => 0,
                ];
                $idList[] = $objStat->book_id;
            }

            $data[$objStat->book_id]['views'] += $objStat->views;
            $data[$objStat->book_id]['favors'] += $objStat->favors;
            $data[$objStat->book_id]['recommend'] += $objStat->recommend;
            $data[$objStat->book_id]['expend_paid'] += $objStat->expend_paid;
            $data[$objStat->book_id]['expend_user'] += $objStat->expend_user;
            $data[$objStat->book_id]['expend_num'] += $objStat->expend_num;
            $data[$objStat->book_id]['pay_income'] += $objStat->pay_income;
            $data[$objStat->book_id]['pay_user'] += $objStat->pay_user;
            $data[$objStat->book_id]['pay_num'] += $objStat->pay_num;

        }
        $data = array_values($data);
        $len = count($data);

        switch ($sort) {
            case 2:
                $sortField = 'pay_income';
                break;
            case 3:
                $sortField = 'favors';
                break;
            case 1:
            default:
                $sortField = 'expend_paid';
                break;
        }

        // 排序
        for ($i=1; $i<$len; $i++) {
            for ($k=0;$k<$len-$i;$k++) {
                if ($data[$k][$sortField] < $data[$k+1][$sortField]) {
                    $tmp = $data[$k+1];
                    $data[$k+1] = $data[$k];
                    $data[$k] = $tmp;
                }
            }
        }
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['id', 'name'],
            ],
        ]);
        $searchModel = new BookStatSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        return $this->render('book', [
            'searchModel' => $searchModel,
            'dataProvider' => $provider
        ]);
    }

    /**
     * 漫画统计
     * @return string
     */
    public function actionComic() {
        $keyword = isset(Yii::$app->request->queryParams['keyword']) ? Yii::$app->request->queryParams['keyword'] : '';
        $sort = isset(Yii::$app->request->queryParams['sort']) ? Yii::$app->request->queryParams['sort'] : 1;

        $tb = Comic::tableName();
        $objComicStat = ComicStat::find()
            ->joinWith('comic')
            ->andFilterWhere(
                [
                    'OR',
                    ['like', $tb.'.name', $keyword],
                    ['like', $tb.'.author', $keyword],
                    ['like', $tb.'.source', $keyword],
                ]
            )
            ->andWhere(['>', 'date', date('Ymd', strtotime("-90 day"))])
            ->all();

        $data = [];
        foreach ($objComicStat as $objStat) {
            //已删除漫画 不展示
            if (!$objStat->comic) {
                continue;
            }
            if (!isset($data[$objStat->comic_id])) {
                $data[$objStat->comic_id] = [
                    'comic' => $objStat->comic,
                    'comic_id' => $objStat->comic_id,
                    'name' => $objStat->comic ? $objStat->comic->name : '',
                    'views' => 0,
                    'favors' => 0,
                    'recommend' => 0,
                    'expend_paid' => 0,
                    'expend_user' => 0,
                    'expend_num' => 0,
                    'pay_income' => 0,
                    'pay_user' => 0,
                    'pay_num' => 0,
                ];
                $idList[] = $objStat->comic_id;
            }
            $data[$objStat->comic_id]['views'] += $objStat->views;
            $data[$objStat->comic_id]['favors'] += $objStat->favors;
            $data[$objStat->comic_id]['recommend'] += $objStat->recommend;
            $data[$objStat->comic_id]['expend_paid'] += $objStat->expend_paid;
            $data[$objStat->comic_id]['expend_user'] += $objStat->expend_user;
            $data[$objStat->comic_id]['expend_num'] += $objStat->expend_num;
            $data[$objStat->comic_id]['pay_income'] += $objStat->pay_income;
            $data[$objStat->comic_id]['pay_user'] += $objStat->pay_user;
            $data[$objStat->comic_id]['pay_num'] += $objStat->pay_num;

        }
        $data = array_values($data);

        $len = count($data);
        switch ($sort) {
            case 2:
                $sortField = 'pay_income';
                break;
            case 3:
                $sortField = 'favors';
                break;
            case 1:
            default:
                $sortField = 'expend_paid';
                break;
        }

        // 排序
        for ($i=1; $i<$len; $i++) {
            for ($k=0;$k<$len-$i;$k++) {
                if ($data[$k][$sortField] < $data[$k+1][$sortField]) {
                    $tmp = $data[$k+1];
                    $data[$k+1] = $data[$k];
                    $data[$k] = $tmp;
                }
            }
        }

        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['id', 'name'],
            ],
        ]);
        $comicSearch = new ComicStatSearch();
        $comicSearch->load(Yii::$app->request->queryParams);
        return $this->render('comic', [
            'dataProvider' => $provider,
            'searchModel' => $comicSearch,
        ]);
    }

    /**
     * 听书统计
     * @return string
     */
    public function actionAudio(){
        $keyword = isset(Yii::$app->request->queryParams['keyword']) ? Yii::$app->request->queryParams['keyword'] : '';
        $sort = isset(Yii::$app->request->queryParams['sort']) ? Yii::$app->request->queryParams['sort'] : 1;

        $tb = Audio::tableName();
        $objAudioStat = AudioStat::find()
            ->joinWith('audio')
            ->andFilterWhere(
                [
                    'OR',
                    ['like', $tb.'.name', $keyword],
                    ['like', $tb.'.author', $keyword],
                    ['like', $tb.'.source', $keyword],
                ]
            )
            ->andWhere(['>', 'date', date('Ymd', strtotime("-90 day"))])
            ->all();

        $data = [];
        /** @var AudioStat $objStat */
        foreach ($objAudioStat as $objStat) {
            if (!$objStat->audio) {
                continue;
            }
            if (!isset($data[$objStat->audio_id])) {
                $data[$objStat->audio_id] = [
                    'audio' => $objStat->audio,
                    'audio_id' => $objStat->audio_id,
                    'name' => $objStat->audio ? $objStat->audio->name : '',
                    'views' => 0,
                    'favors' => 0,
                    'recommend' => 0,
                    'expend_paid' => 0,
                    'expend_user' => 0,
                    'expend_num' => 0,
                    'pay_income' => 0,
                    'pay_user' => 0,
                    'pay_num' => 0,
                ];
                $idList[] = $objStat->audio_id;
            }

            $data[$objStat->audio_id]['views'] += $objStat->views;
            $data[$objStat->audio_id]['favors'] += $objStat->favors;
            $data[$objStat->audio_id]['recommend'] += $objStat->recommend;
            $data[$objStat->audio_id]['expend_paid'] += $objStat->expend_paid;
            $data[$objStat->audio_id]['expend_user'] += $objStat->expend_user;
            $data[$objStat->audio_id]['expend_num'] += $objStat->expend_num;
            $data[$objStat->audio_id]['pay_income'] += $objStat->pay_income;
            $data[$objStat->audio_id]['pay_user'] += $objStat->pay_user;
            $data[$objStat->audio_id]['pay_num'] += $objStat->pay_num;

        }
        $data = array_values($data);
        $len = count($data);

        switch ($sort) {
            case 2:
                $sortField = 'pay_income';
                break;
            case 3:
                $sortField = 'favors';
                break;
            case 1:
            default:
                $sortField = 'expend_paid';
                break;
        }
        // 排序
        for ($i=1; $i<$len; $i++) {
            for ($k=0;$k<$len-$i;$k++) {
                if ($data[$k][$sortField] < $data[$k+1][$sortField]) {
                    $tmp = $data[$k+1];
                    $data[$k+1] = $data[$k];
                    $data[$k] = $tmp;
                }
            }
        }
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['id', 'name'],
            ],
        ]);
        $searchModel = new AudioStatSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        return $this->render('audio', [
            'searchModel' => $searchModel,
            'dataProvider' => $provider
        ]);
    }
}
