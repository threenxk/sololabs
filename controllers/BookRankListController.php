<?php
namespace admin\controllers;

use admin\models\book\BookRank;
use admin\models\book\BookRankList;
use Yii;

class BookRankListController extends BaseController
{
    public $name = '榜单作品';

    public $modelClass = 'admin\models\book\BookRankList';
    public $searchModelClass = 'admin\models\book\BookRankListSearch';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        $actions['create']['redirect'] = ['index', 'rank_id' => $this->rank->id];
        $actions['update']['redirect'] = ['index', 'rank_id' => $this->rank->id];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function breadcrumbs()
    {
        $breadcrumbs = [];

        $rank = $this->rank;
        $this->name = '小说'.$this->name;

        foreach (['index', 'create', 'update'] as $actionID) {
            $breadcrumbs[$actionID][] = ['label' => '排行榜列表', 'url' => ['/book-rank/index']];
            $breadcrumbs[$actionID][] = ['label' => $rank->fullName, 'url' => ['index', 'rank_id' => $rank->id]];
            if ($actionID == 'index') {
                continue;
            }
            $breadcrumbs[$actionID][] = $this->getPageTitle($actionID);
        }

        return $breadcrumbs;
    }

    /**
     * @inheritdoc
     */
    public function actionButtons()
    {
        return [
            [
                'label'   => '新增排行榜作品',
                'url'     => ['create', 'rank_id' => $this->rank->id],
                'options' => ['class' => 'btn green'],
            ],
        ];
    }

    public function actionEditButtons()
    {
        return [];
    }

    /**
     * 获取榜单
     */
    protected function getRank()
    {
        if ($rankId = Yii::$app->request->get('rank_id')) {
            return BookRank::findOne($rankId);
        }

        // 设置位置时只有id，通过id获取rank id，再去查询
        $id = Yii::$app->request->get('id');
        $rankBook = BookRankList::findOne($id);
        return BookRank::findOne($rankBook->rank_id);
    }
}
