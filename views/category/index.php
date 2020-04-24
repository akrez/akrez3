<?php

use app\models\CategorySearch;
use app\models\Status;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Categories');
$this->registerCss('
    .table thead tr th {
        min-height: 52px;
    }
');
?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<?=
$this->render('_form', [
    'model' => $newModel,
]);
?>

<div class="table-responsive">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}{summary}<br>{pager}",
        'tableOptions' => ['class' => 'table table-hover table-bordered table-striped'],
        'columns' => [
            'title',
            [
                'attribute' => 'status',
                'format' => 'status',
                'filter' => Status::getDefaults(),
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetimefa',
                'filter' => false,
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), Url::current(['category/update', 'id' => $model->id]), [
                                'class' => 'btn btn-info btn-block btn-social',
                    ]);
                },
            ],
            [
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    if ($model->status == Status::STATUS_ACTIVE) {
                        return Html::a(' <span class="glyphicon glyphicon-ban-circle"></span> ' . Status::getLabel(Status::STATUS_DISABLE), Url::current([0 => 'category/status', 'id' => $model->id, 'status' => Status::STATUS_DISABLE]), ['class' => 'btn btn-warning btn-block btn-social']);
                    } elseif ($model->status == Status::STATUS_DISABLE) {
                        return Html::a(' <span class="glyphicon glyphicon-ok-circle"></span> ' . Status::getLabel(Status::STATUS_ACTIVE), Url::current([0 => 'category/status', 'id' => $model->id, 'status' => Status::STATUS_ACTIVE]), ['class' => 'btn btn-primary btn-block btn-social']);
                    }
                    return '';
                },
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Fields'), Url::current(['field/index', 'parent_id' => $model->id]), [
                                'class' => 'btn btn-default btn-block btn-social',
                    ]);
                },
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-grain"></span> ' . Yii::t('app', 'Products'), Url::current([0 => 'product/index', 'parent_id' => $model->id]), [
                                'class' => 'btn btn-default btn-block btn-social',
                                'data-pjax' => '0',
                    ]);
                },
            ],
        ],
    ]);
    ?>

</div>
