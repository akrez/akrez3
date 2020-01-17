<?php
namespace app\assets;

use yii\web\AssetBundle;

class SiteAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'cdn/css/bootstrap-social.css',
        'cdn/css/font-sahel.css',
        'cdn/css/admin.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\BootstrapAsset',
        //'yii\bootstrap\BootstrapThemeAsset',
    ];
}
