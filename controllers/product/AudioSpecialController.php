<?php
namespace admin\controllers\product;

use admin\models\audio\AudioSpecial;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class AudioSpecialController extends ProductBaseController
{
    public $name = '推荐听书';
    public $modelClass = 'admin\models\audio\AudioSpecial';
    public $searchModelClass = 'admin\models\audio\AudioSpecialSearch';

    public function actions()
    {
        $actions = parent::actions();

        // 修改新增和修改时返回的页面
        $actions['create']['redirect'] = Url::to(['index', 'position' => Yii::$app->request->get('position')]);
        $actions['update']['redirect'] = Url::to(['index', 'position' => Yii::$app->request->get('position')]);

        return $actions;
    }

    /**
     * index页操作按钮
     * @return array
     */
    public function actionButtons()
    {
        return [
            [
                'label'   => '新增' . $this->name,
                'url'     => ['create', 'position' => Yii::$app->request->get('position', AudioSpecial::POSITION_SHELF)],
                'options' => ['class' => 'btn green'],
            ],
        ];
    }

    /**
     * 页面标题
     * @return array
     */
    public function pageTitles()
    {
        $position = Yii::$app->request->get('position');
        $positionName = ArrayHelper::getValue(AudioSpecial::$positionMap, $position) . '-' . $this->name;

        return [
            'index'  => "{$this->name}列表",
            'view'   => "查看{$this->name}详情",
            'create' => "新增{$positionName}",
            'update' => "编辑{$positionName}",
        ];
    }
}
