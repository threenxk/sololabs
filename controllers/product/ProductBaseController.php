<?php
namespace admin\controllers\product;

use Yii;
use admin\controllers\BaseController;

/**
 * Product Base Controller
 */
class ProductBaseController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function getViewBasePath()
    {
        return '@admin/views/product/' . $this->id;
    }

    /**
     * @inheritdoc
     */
    public function pageTitles()
    {
        $name = $this->name;
        
        return [
            'index'  => "{$name}列表",
            'view'   => "查看{$name}详情",
            'create' => "新增{$name}",
            'update' => "编辑{$name}",
        ];
    }

    /**
     * 面包屑导航条
     * @return array
     */
    public function breadcrumbs()
    {
        $breadcrumbs = [];

        foreach (['index', 'view', 'create', 'update'] as $actionID) {
            
            $label = $this->name;
           /* if (Yii::$app->controller->module) {
                if (Yii::$app->controller->module->name == 'App' && $label == 'Banner图') {
                    $ch = ' ';
                } else {
                    $ch = '';
                }
                $label = Yii::$app->controller->module->name . $ch . $label;
            }*/
            $breadcrumbs[$actionID][] = ['label' => $label . '列表', 'url' => ['index']];
            if ($actionID == 'index') { // 首页不需要列表，跳过
                continue;
            }
            $breadcrumbs[$actionID][] = $this->getPageTitle($actionID);
        } 

        return $breadcrumbs;
    }

}
