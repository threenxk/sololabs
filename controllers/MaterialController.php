<?php
namespace admin\controllers;

use admin\models\drp\DrpMaterial;
use admin\models\drp\DrpUrl;
use admin\models\drp\Material;
use common\helpers\OssUrlHelper;
use Yii;

/**
 * 素材管理
 */
class MaterialController extends BaseController
{
    public $name = '素材';

    public $modelClass = 'admin\models\drp\DrpMaterial';
    public $searchModelClass = 'admin\models\drp\DrpMaterialSearch';

    /**
     * 获取素材
     */
    public function actionGetMaterial()
    {
        $type = Yii::$app->request->get('type', 1);

        $list = DrpMaterial::find()
            ->where(['type' => $type])
            ->orderBy('id desc')
            ->asArray()
            ->all();
        foreach ($list as &$item) {
            if ($item['type'] == DrpMaterial::TYPE_IMAGE) { // 如果是图片类型
                $item['image_url'] = OssUrlHelper::set($item['image_url'])->toUrl();
            }
        }

        exit(json_encode($list));
    }

    /**
     * 获取推广链接
     */
    public function actionGetDrpurl()
    {
        $url = Yii::$app->request->get('url');

        $drpUrl = DrpUrl::find()->select('id, book_id, url')->where(['url' => $url])->one();
        $name = $drpUrl->book->name;
        $description = $drpUrl->book->description;
        $drpUrl = $drpUrl->toArray();
        $drpUrl['name'] = $name;
        $drpUrl['description'] = $description;

        exit(json_encode($drpUrl));
    }

}
