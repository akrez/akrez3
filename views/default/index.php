<?php

use app\assets\ChartJsAsset;
use app\components\AdminHelper;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::$app->blog->getIdentity()->title;
$model = Yii::$app->blog->getIdentity();
ChartJsAsset::register($this);
$this->registerJs("", View::POS_LOAD);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-2 pb20">
                <a href="<?= AdminHelper::url(['gallery-logo/index']) ?>">
                    <?= Html::img(($model->logo ? AdminHelper::getImageUrl('logo', '400', $model->logo) : '@web/cdn/image/logo.png'), ['class' => 'img img-responsive', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
                </a>
            </div>
            <div class="col-sm-10 pb20">
                <?= Html::tag('h3', Html::encode($model->title), ['style' => 'margin-top: 0;']) ?>
                <?= ($model->slug ? Html::tag('h4', Html::encode($model->slug)) : '') ?>
                <?= ($model->des ? Html::tag('p', Html::encode($model->des), ['style' => 'text-align: justify;']) : '') ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <canvas id="canvas" height="85"></canvas>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function (event) {

        var config = {
            type: 'line',
            data: {
                labels: <?= json_encode($chartData['labels']) ?>,
                datasets: [
                    {
                        label: 'بدون دسته بندی',
                        fill: false,
                        borderColor: '#ff6a8a',
                        backgroundColor: '#ffb1c1',
                        spanGaps: true,
                        data: <?= json_encode($chartData['dontHaveActionPrimaryCount']) ?>,
                    },
                    {
                        label: 'با دسته بندی خاص',
                        fill: false,
                        borderColor: '#53afee',
                        backgroundColor: '#9ad0f5',
                        spanGaps: true,
                        data: <?= json_encode($chartData['haveActionPrimaryCount']) ?>,
                    },
                    {
                        label: 'مجموع',
                        fill: true,
                        borderColor: '#993799',
                        backgroundColor: 'rgba(209,165,209,0.25)',
                        spanGaps: true,
                        data: <?= json_encode($chartData['sumCount']) ?>
                    },
                ]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom',
                    rtl: true
                },
                title: {
                    display: true,
                    text: 'تعداد جستجو'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    rtl: true
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    if (value % 1 === 0) {
                                        return value;
                                    }
                                }
                            }
                        }]
                }
            },
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'Sahel'",
        };
        var ctx = document.getElementById('canvas').getContext('2d');
        new Chart(ctx, config);
    });
</script>

