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
                    return Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::current([0 => 'category/remove', 'id' => $model->id]), [
                                'class' => 'btn btn-danger btn-block btn-social',
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);
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
                    return Html::a(' <span class="glyphicon glyphicon-tag"></span> ' . Yii::t('app', 'Garanties'), Url::current([0 => 'category-garanties/index', 'id' => $model->id]), [
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
