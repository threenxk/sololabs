<?php

namespace admin\controllers;

use admin\models\comic\ComicRank;
use admin\models\comic\ComicRankList;
use Yii;

class ComicRankListController extends BaseController
{
    public $name = '榜单作品';

    public $modelClass = 'admin\models\comic\ComicRankList';
    public $searchModelClass = 'admin\models\comic\ComicRankListSearch';

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
        $this->name = '漫画'.$this->name;

        foreach (['index', 'create', 'update'] as $actionID) {
            $breadcrumbs[$actionID][] = ['label' => '排行榜列表', 'url' => ['/comic-rank/index']];
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
            return ComicRank::findOne($rankId);
        }

        // 设置位置时只有id，通过id获取rank id，再去查询
        $id = Yii::$app->request->get('id');
        $rankBook = ComicRankList::findOne($id);
        return ComicRank::findOne($rankBook->rank_id);
    }
}
