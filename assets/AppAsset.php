<?php

namespace admin\assets;

use yii\web\AssetBundle;

/**
 * Main admin application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $css = [
        'css/main.css?v=' . CSS_FILE_VERSION,
        'css/webuploader.css?v=' . CSS_FILE_VERSION,
    ];
    public $js = [
        'js/common/repeat-submit.js?v=' . JS_FILE_VERSION, // 防止重复提交
        'js/common/remove-emoji.js?v=' . JS_FILE_VERSION, // 禁止填写表情
        'js/common/go-page.js?v=' . JS_FILE_VERSION, // 页面跳转问题
        'js/common/sort-display.js?v=' . JS_FILE_VERSION, // 排序问题
        'js/webuploader.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'metronic\assets\CommonAsset',
    ];
}
