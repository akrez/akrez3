<?php
namespace app\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'cdn/css/adminlte.min.css',
        'cdn/css/_all-skins.min.css',
        'cdn/css/font-sahel.css',
        'cdn/css/admin.css',
    ];
    public $js = [
        'cdn/js/app.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'app\assets\BootstrapAsset',
        'yii\bootstrap\BootstrapThemeAsset',
    ];

}
