<?php
namespace admin\controllers;

use admin\models\audio\AudioRank;
use admin\models\audio\AudioRankList;
use Yii;
class AudioRankListController extends BaseController
{
    public $name = '榜单';

    public $modelClass = 'admin\models\audio\AudioRankList';
    public $searchModelClass = 'admin\models\audio\AudioRankListSearch';

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


    public function breadcrumbs()
    {
        $breadcrumbs = [];

        $rank = $this->rank;
        $this->name = '听书'.$this->name;

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
            return AudioRank::findOne($rankId);
        }

        // 设置位置时只有id，通过id获取rank id，再去查询
        $id = Yii::$app->request->get('id');
        $rankBook = AudioRankList::findOne($id);
        return AudioRank::findOne($rankBook->rank_id);
    }

}
