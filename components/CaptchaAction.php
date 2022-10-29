<?php

namespace admin\components;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    /**
     * 生成纯数字的验证码
     */
    protected function generateVerifyCode()
    {
        return (string)rand(1000, 9999);
    }
}
