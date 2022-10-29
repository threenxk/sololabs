<?php
namespace admin\components;
use common\helpers\OssUrlHelper;
use common\helpers\Tool;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use common\helpers\OssHelper;

/**
 * 文件上传处理
 */
class Upload extends Model
{
    private $file;


    public function upImages($dir)
    {
        $model = new static;
        $model->file = UploadedFile::getInstanceByName('file');
        if (!$model->file) { // 没有获取到文件直接返回
            return false;
        }

        if ($model->validate()) {
            $oss = new OssHelper();
            $imgPath = $dir . md5(time().rand(10000, 99999)).'.' . $model->file->extension;
            $oss->uploadFile($model->file->tempName, $imgPath);
            return [
                'code' => 0,
                'url' => OssUrlHelper::set($imgPath)->toUrl(),
                'attachment' => $imgPath
            ];
        } else {
            $errors = $model->errors;
            return [
                'code' => 1,
                'msg' => current($errors)[0]
            ];
        }
    }
}
