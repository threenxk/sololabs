<?php
namespace admin\controllers;
use common\helpers\OssHelper;
use common\helpers\OssUrlHelper;
use common\helpers\Tool;
use yii\helpers\Json;
use \admin\components\Upload;
use yii;

/**
 * 上传类，用于后台批量图片上传
 */
class UploadController extends yii\web\Controller
{
    /**
     * 用于漫画章节图片上传
     */
    public function actionComicChapterImage()
    {
        try {
            $upload = new Upload();
            $info = $upload->upImages('comic/photos/' . date('Ymd') . '/');

            $info && is_array($info) ?
                exit(Json::htmlEncode($info)) :
                exit(Json::htmlEncode([
                    'code' => 1,
                    'msg' => 'error'
                ]));


        } catch (\Exception $e) {
            exit(Json::htmlEncode([
                'code' => 1,
                'msg' => $e->getMessage()
            ]));
        }
    }

    /**
     * 单个上传文件
     */
    public function actionFile()
    {
        $fileInfo = $_FILES['file']; // 文件信息

        // 文件名相关信息
        $extName = $this->_extName($fileInfo['name']);
        $newName = 'files/' . date('Ymd') . '/' .Tool::getRandKey() . '.' . $extName;

        return $this->actionUploadFile($fileInfo, $newName);
    }


    /**
     * 单个上传文件
     */
    public function actionLogoIcon()
    {
        $fileInfo = $_FILES['file']; // 文件信息

        // 文件名相关信息
        $extName = $this->_extName($fileInfo['name']);
        $newName = 'apps-info/icon/' . date('Ymd') . '/' .Tool::getRandKey() . '.' . $extName;
        return $this->actionUploadFile($fileInfo, $newName);
    }

    public function actionUploadFile($fileInfo, $newName)
    {
        $ossHelper = new OssHelper();
        $ret = $ossHelper->uploadFile($fileInfo['tmp_name'], $newName);
        if (!$ret) {
            Tool::responseJson(1, '上传失败');
        }

        return Tool::responseJson(0, 'success', ['path' => OssUrlHelper::set($newName)->toUrl(), 'url' => $newName]);
    }

    /**
     * 大文件分片上传
     */
    public function actionDividePiece()
    {
        $dir = 'divide-piece/' . date('Ymd'); // 存放临时文件地方

        $post = Yii::$app->request->post();

        // 文件名命名为文件MD5内容，可以保证唯一，再添加上扩展名
        $fileName = md5($post['name']) .'.' . $this->_extName($post['name']);

        if (!isset($post['chunks'])) { // 如果没有设置chunks，表示文件小于最小分片，直接上传整个文件
            $ossHelper = new OssHelper();
            $ossHelper->uploadFile($_FILES['file']['tmp_name'], $dir . '/' . $fileName);
            return json_encode(['status' => 2,'url' =>$dir.'/'.$fileName]);
        }

        if ($post['chunk'] == 0) { // 第一个，先删除已有的文件，防止之前已经上传过的影响
            @unlink($dir . '/' . $fileName);
        }

        // 上传oss
        $ossHelper = new OssHelper();
        // todo 文件上传失败处理
        $ossHelper->pieceUpload($_FILES['file']['tmp_name'], $dir . '/' . $fileName, $post['chunk']);

        if ($post['chunks'] == ($post['chunk']+1)) { // 上传成功，chunks为文件总数，chunk为文件下标，从0开始
            return json_encode(['status' => 2,'url' =>$dir.'/'.$fileName]);
        }
        return json_encode(['status' => 1]);
    }


    /**
     * 本地文件上传
     */
    public function actionNewsDivide()
    {
        $tmpdir = 'divide-piece/' . date('Ymd'); // 存放临时文件地方

        $post = Yii::$app->request->post();

        // 文件名命名为文件MD5内容，可以保证唯一，再添加上扩展名
        $fileName = md5($post['name']) .'.' . $this->_extName($post['name']);

        // 先把文件存储到本地
        $rootPath = UPLOAD_FILE_DIR . '/'; //文件存储根路径

        if (($pos = strrpos($tmpdir . '/' . $fileName, '/')) !== false) { //存储的文件夹
            $dir = substr($tmpdir . '/' . $fileName, 0, $pos);
            if (!is_dir($rootPath . $dir)) {
                @mkdir($rootPath . $dir, 0777, true);
            }
        }
        file_put_contents($rootPath.$tmpdir . '/' . $fileName, file_get_contents($_FILES['file']['tmp_name']), FILE_APPEND);

        return json_encode(['status' => 2,'url' =>$dir.'/'.$fileName]);
    }

    /**
     * 根据文件名名来获取扩展名
     * @param $name
     * @return mixed
     */
    private function _extName($name)
    {
        $nameInfo = explode('.', $name);
        return end($nameInfo);
    }
}
