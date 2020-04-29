<?php

use app\assets\ChartJsAsset;
use app\components\AdminHelper;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
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

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <?=
            GridView::widget([
                'dataProvider' => $chartSummaryDataProvider,
                'columns' => [
                    'id',
                    'created_date',
                    'created_time',
                    [
                        'attribute' => 'user_id',
                        'value' => function ($model) use($list) {
                            if (isset($list['customers'][$model->user_id])) {
                                return $list['customers'][$model->user_id]->email;
                            }
                        }
                    ],
                    'user_agent',
                    'ip',
                    [
                        'attribute' => 'category_id',
                        'value' => function ($model) use($list) {
                            if (isset($list['categories'][$model->category_id])) {
                                return $list['categories'][$model->category_id]->title;
                            }
                        }
                    ]
                ],
                'summary' => false,
            ])
            ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function (event) {

        var config = {
            type: 'line',
            data: {
                labels: <?= json_encode($chartSummaryData['labels']) ?>,
                datasets: [
                    {
                        label: 'بدون دسته بندی',
                        fill: false,
                        borderColor: '#ff6a8a',
                        backgroundColor: '#ffb1c1',
                        spanGaps: true,
                        data: <?= json_encode($chartSummaryData['dontHaveCategoryId']) ?>,
                    },
                    {
                        label: 'با دسته بندی خاص',
                        fill: false,
                        borderColor: '#53afee',
                        backgroundColor: '#9ad0f5',
                        spanGaps: true,
                        data: <?= json_encode($chartSummaryData['haveCategoryId']) ?>,
                    },
                    {
                        label: 'مجموع',
                        fill: true,
                        borderColor: '#993799',
                        backgroundColor: 'rgba(209,165,209,0.25)',
                        spanGaps: true,
                        data: <?= json_encode($chartSummaryData['sum']) ?>
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
                            beginAtZero: true
                        }
                    }],
                    xAxes: [
                        {
                            ticks: {
                                maxRotation: 90,
                                minRotation: 90
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

