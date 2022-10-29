<?php
namespace admin\controllers\product;

use admin\models\book\BookRecommendList;
use Yii;

class BookRecommendListController extends ProductBaseController
{
    public $name = '推荐位作品';
    public $modelClass = 'admin\models\book\BookRecommendList';
    public $searchModelClass = 'admin\models\book\BookRecommendListSearch';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();

        $actions['create']['redirect'] = ['index', 'recommend_id' => $this->recommend->id];
        $actions['update']['redirect'] = ['index', 'recommend_id' => $this->recommend->id];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function breadcrumbs()
    {
        $breadcrumbs = [];

        $recommend = $this->recommend;

        foreach (['index', 'create', 'update'] as $actionID) {
            $breadcrumbs[$actionID][] = ['label' => '推荐位列表', 'url' => ['book-recommend/index']];
            if ($actionID == 'index') { // 首页不可点击面包屑
                $breadcrumbs[$actionID][] = $recommend->fullName;
                continue;
            } else {
                $breadcrumbs[$actionID][] = ['label' => $recommend->fullName, 'url' => ['index', 'recommend_id' => $recommend->id]];
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
                'label'   => '新增推荐位作品',
                'url'     => ['create', 'recommend_id' => $this->recommend->id],
                'options' => ['class' => 'btn green'],
            ],
        ];
    }

    //清空编辑处按钮
    public function actionEditButtons()
    {
        return [];
    }

    /**
     * 获取推荐位
     * @return Recommend
     */
    protected function getRecommend()
    {
        $class = '\admin\models\book\BookRecommend';
        $recommendId = Yii::$app->request->get('recommend_id');
        if (!$recommendId) { // 没有则通过id获取
            $recommend = BookRecommendList::findOne(Yii::$app->request->get('id'));
            $recommendId = $recommend->recommend_id;
        }

        return call_user_func([$class, 'findOne'], $recommendId);
    }
}
