<?php

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'cdn/ChartJs/Chart.min.css',
    ];
    public $js = [
        'cdn/ChartJs/Chart.min.js',
    ];
    public $depends = [
        'app\assets\AdminAsset',
    ];

}
