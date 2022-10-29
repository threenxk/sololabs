<?php

namespace admin\components\webuploder;

use common\helpers\OssHelper;
use Yii;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\helpers\Json;

class FileInput extends InputWidget
{
    public $clientOptions = [];   // 配置，默认多图
    public $chooseButtonClass = ['class' => 'btn-default green'];
    private $_view;
    private $_hashVar;
    private $_encOptions;
    private $_config;

    public $domain;

    public function init ()
    {
        $this->domain = Yii::$app->setting->get('oss.server_point') . '/';

        parent::init();
        $this->_view = $this->getView();
        $this->initOptions();
        $this->initConfig();
        $this->registerClientScript();
    }

    public function run ()
    {
        if ($this->hasModel()) {
            $model = $this->model;
            $attribute = $this->attribute;

            // 单图上传，目前不需要
//            if (empty($this->_config['pick']['multiple'])) {
//                $html = $this->renderInput($model, $attribute);
//                $html .= $this->renderImage($model, $attribute);
//            }
            // 多图上传
//            else {
                $html = $this->renderMultiInput($model, $attribute);
                $html .= $this->renderMultiImage($model, $attribute);
//            }
            
            echo $html;
        }
    }

    /**
     * init options
     */
    public function initOptions ()
    {
        // to do.
        $id = md5($this->options['id']);
        $this->hashClientOptions("webupload_config_{$id}");
    }

    /**
     * register base js config
     */
    public function initConfig ()
    {
        if (empty($this->domain)) {
            throw new InvalidConfigException("param `domain` must set.", 1);
        }
        $this->_config = $this->mergeConfig();
        $config = Json::htmlEncode($this->_config);
        $js = <<<JS
            var {$this->_hashVar} = {$config};
            $('#{$this->_hashVar}').webupload_fileinput({$this->_hashVar});
JS;
        $this->_view->registerJs($js);
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript ()
    {
        FileInputAsset::register($this->_view);
    }

    /**
     * generate hash var by plugin options
     */
    protected function hashClientOptions($name)
    {
        $this->_encOptions = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
        $this->_hashVar = $name . '_' . hash('crc32', $this->_encOptions);
    }

    public function mergeConfig ()
    {
        // $config = $this->mergeArray($this->getDefaultClientOptions(), $this->clientOptions);
        $config = array_merge($this->getDefaultClientOptions(), $this->clientOptions);
        if (isset($this->clientOptions['csrf']) && $this->clientOptions['csrf'] === false) {
        } else {
            $config['formData'][Yii::$app->request->csrfParam] = Yii::$app->request->csrfToken;
        }

        $config['modal_id'] = $this->_hashVar;

        if (empty($config['server'])) {
            $uploadUrl = Yii::$app->params['webuploader']['uploadUrl'];
            $config['server'] = Url::to([$uploadUrl, $config['formData']]);
        }

        return $config;
    }

    /**
     * array merge
     */
    public function mergeArray ($oriArr, $desArr)
    {
        foreach ($oriArr as $k => $v) {
            if (array_key_exists($k, $desArr)) {
                if (is_array($v) && $v) {
                    foreach ($v as $k2 => $v2) {
                        if (array_key_exists($k2, $desArr[$k])) {
                            $oriArr[$k][$k2] = $desArr[$k][$k2];
                        }
                    }
                } else {
                    $oriArr[$k] = $desArr[$k];
                }
            }
        }
        return $oriArr;
    }

    /**
     * register default config for js
     */
    public function getDefaultClientOptions ()
    {
        return Yii::$app->params['webuploader']['baseConfig'];
    }

    /**
     * render html body-input
     */
    public function renderInput ($model, $attribute)
    {
        Html::addCssClass($this->chooseButtonClass, "btn {$this->_hashVar}");
        $eles = [];
        $eles[] = Html::activeTextInput($model, $attribute, ['class' => 'form-control']);
        $eles[] = Html::tag('span', Html::button('选择图片', $this->chooseButtonClass), ['class' => 'input-group-btn']);

        return Html::tag('div', implode("\n", $eles), ['class' => 'input-group']);
    }

    /**
     * render html body-input-multi
     */
    public function renderMultiInput ($model, $attribute)
    {
        $inputName = Html::getInputName($model, $attribute);
        Html::addCssClass($this->chooseButtonClass, "btn {$this->_hashVar}");

        // 链接上传样式
        $imageText = [];
        $imageText[] = Html::textarea($attribute, null, ['class' => 'form-control', 'rows' => '5']);
        $imageText[] = Html::tag('span', Html::button('确定上传', ['class' => 'btn-default btn green upload_image_url', 'style' => ['margin-left' => '1px', 'bottom' => '0', 'position' => 'absolute']]), ['class' => 'input-group-btn']);
        $html = Html::tag('div', implode("\n", $imageText), ['class' => 'input-group upload_image_text', 'style' => ['display' => 'none', 'width' => '100%']]);

        // 多图上传样式
        $multiMp = [];
        $multiMp[] = Html::textInput($attribute, null, ['class' => 'form-control', 'readonly' => 'readonly']);
        $multiMp[] = Html::hiddenInput($inputName, null, ['id' => Html::getInputName($model, 'id')]);
        $multiMp[] = Html::tag('span', Html::button('选择图片', $this->chooseButtonClass), ['class' => 'input-group-btn']);
        $html .= Html::tag('div', implode("\n", $multiMp), ['class' => 'input-group upload_multi_mp']);

        return $html;
    }

    /**
     * render html body-image
     */
    public function renderImage ($model, $attribute)
    {
        $src = Yii::$app->params['webuploader']['baseConfig']['defaultImage'];
        $eles = [];
        if (($value = $model->$attribute)) {
            $src = $this->_validateUrl($value) ? $value : $this->domain . $value;
        }
        $eles[] = Html::img($src, ['class' => 'img-responsive img-thumbnail cus-img']);
        $eles[] = Html::tag('em', 'x', ['class' => 'close delImage', 'title' => '删除这张图片']);

        return Html::tag('div', implode("\n", $eles), ['class' => 'input-group', 'style' => 'margin-top:.5em;']);
    }

    /**
     * render html body-image-muitl
     */
    public function renderMultiImage ($model, $attribute)
    {
        /**
         * @var $srcTmp like this: src1,src2...srcxxx
         */
        $srcTmp = $model->$attribute;
        $items[] = Html::tag('div', '', ['class' => 'multi-item', 'style' => ['display' => 'none']]);;
        if ($srcTmp) {
            is_string($srcTmp) && $srcTmp = explode(Yii::$app->params['webuploader']['delimiter'], $srcTmp);
            $inputName = Html::getInputName($model, $attribute);
            $ids = Html::getInputName($model, 'id');
            foreach ($srcTmp as $k => $v) {
                $dv = $this->_validateUrl($v) ? $v : $this->domain . $v;
                $src = $v ? $dv : Yii::$app->params['webuploader']['baseConfig']['defaultImage'];
                $eles = [];
                $eles[] = Html::img($src, ['class' => 'img-responsive img-thumbnail cus-img', 'data-toggle' => 'modal', 'data-target' => '#select_image']);
                $eles[] = Html::hiddenInput($inputName . "[]", $v);
                $eles[] = Html::hiddenInput($ids . "[]", $k);
                $eles[] = Html::tag('em', 'x', ['class' => 'close delMultiImage', 'title' => '删除这张图片']);
                $items[] = Html::tag('div', implode("\n", $eles), ['class' => 'multi-item']);
            }
        }

        return Html::tag('div', implode("\n", $items), ['class' => 'input-group multi-img-details']);
    }

    /**
     * validate `$value` is url
     */
    private function _validateUrl ($value)
    {
        $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
        $validSchemes = ['http', 'https'];
        $pattern = str_replace('{schemes}', '(' . implode('|', $validSchemes) . ')', $pattern);
        if (!preg_match($pattern, $value)) {
            return false;
        }
        return true;
    }
}
