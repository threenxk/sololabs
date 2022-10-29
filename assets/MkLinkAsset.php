<?php
namespace admin\assets;

use yii\web\AssetBundle;

class MkLinkAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/toastr.min.css',
        'css/bootstrap.min.css',
        'css/font-awesome.css',
        'css/wechat.css',
        'css/layer.css'
    ];
    public $js = [
        'js/bootstrap.min.js',
        'js/layer.js',
        'js/clipboard.min.js',
        'js/html2canvas.min.js',
        'js/html2canvas.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
