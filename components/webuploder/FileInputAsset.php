<?php

namespace admin\components\webuploder;

use yii\web\AssetBundle;

class FileInputAsset extends AssetBundle
{
    public $css = [
    	'webuploader/style.css',
        'webuploader/webuploader.css',
        'css/style.css',
    ];
    public $js = [
        'webuploader/webuploader.min.js',
        'webuploader/init.js?v=1.2'
    ];
    public $depends = [
//        'yii\bootstrap\BootstrapPluginAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }
}
