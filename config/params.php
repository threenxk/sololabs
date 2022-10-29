<?php
define('STATUS_TIPS', '（作品已下架）'); //作品下架提示
// 资源文件版本号
define('CSS_FILE_VERSION', '1.1');
define('JS_FILE_VERSION', '1.0.2');

return [
    'offFilterRoute' => [
        'drp/generalize/index'
    ],

    'webuploader' => [
        // 后端处理图片的地址
        'uploadUrl' => 'upload/comic-chapter-image',
        // 多文件分隔符
        'delimiter' => ',',
        // 基本配置
        'baseConfig' => [
            'defaultImage' => '',    // 默认图
            'disableGlobalDnd' => true,
            'accept' => [
                'title' => '上传图片',
                'extensions' => 'jpg,jpeg,png,gif',
                'mimeTypes' => 'image/*',
            ],
        ],
    ],

    'setting' => [
        'system' => [
            [
                'label' => '基础配置',
                'route' => '/setting/system',
            ],
            [
                'label' => '客服信息',
                'route' => '/setting/service-info',
            ],
            /*[
                'label' => '签到设置',
                'route' => '/setting/update-sign',
            ],*/
            [
                'label' => '图片存储配置',
                'route' => '/setting/oss',
            ],
            [
                'label' => '短信服务设置',
                'route' => '/setting/message',
            ],
        ],

        'app' => [
            [
                'label' => '功能设置',
                'route' => '/setting/base-info',
            ],
            [
                'label' => '三方推送设置',
                'route' => '/setting/app-push',
            ],
            [
                'label' => '登录分享设置',
                'route' => '/setting/login-share',
            ],
            [
                'label' => '三方支付设置',
                'route' => '/setting/three-pay',
            ],
   /*         [
                'label' => '应用信息',
                'route' => '/setting/app-info',
            ],
            [
                'label' => '规则配置',
                'route' => '/setting/app-rule',
            ],
            [
                'label' => '月票设置',
                'route' => '/setting/ticket'
            ],
            [
                'label' => '月票选项',
                'route' => '/setting/ticket-option'
            ],
            [
                'label' => '阿里云推送设置',
                'route' => '/setting/app-push',
            ],
            [
                'label' => '微信登录支付设置',
                'route' => '/setting/wx-setting',
            ],
            [
                'label' => '支付宝设置',
                'route' => '/setting/alipay',
            ],
            [
                'label' => 'APP分享设置',
                'route' => '/setting/app-share',
            ],*/
        ],

        'mp' => [
            [
                'label' => '公众号配置管理',
                'route' => '/setting/mp-info'
            ],
            [
                'label' => '公众号签到设置',
                'route' => '/setting/mp-sign'
            ],
            [
                'label' => '公众号支付设置',
                'route' => '/setting/mp-pay'
            ],
            [
                'label' => 'wap站支付设置',
                'route' => '/setting/wap-pay'
            ],
        ],
    ]
];
