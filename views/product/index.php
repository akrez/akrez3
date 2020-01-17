<?php

use app\components\AdminHelper;
use yii\grid\GridView;
use app\models\Status;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Products') . ': ' . $parentModel->title;
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
            [
                'attribute' => 'image',
                'value' => function ($model, $key, $index, $grid) {
                    return ($model->image ? Html::img(AdminHelper::getImageUrl('product', '_33_100', $model->image)) : '');
                },
                'format' => 'raw',
                'enableSorting' => false,
            ],
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
                    return Html::a(' <span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'Update'), Url::current(['product/update', 'id' => $model->id, 'parent_id' => $model->category_id]), ['class' => 'btn btn-info btn-block btn-social']);
                },
            ],
            [
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::current([0 => 'product/remove', 'id' => $model->id]), [
                                'class' => 'btn btn-danger btn-block btn-social',
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);
                },
                'filter' => false,
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'ProductFields'), Url::current(['fields/index', 'parent_id' => $model->id]), [
                                'class' => 'btn btn-default btn-block btn-social',
                    ]);
                },
                'filter' => false,
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-usd"></span> ' . Yii::t('app', 'Packages'), Url::current([0 => 'package/index', 'parent_id' => $model->id]), [
                                'class' => 'btn btn-default btn-block btn-social',
                    ]);
                },
                'filter' => false,
            ],
            [
                'format' => 'html',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-picture"></span> ' . Yii::t('app', 'ProductGalleries'), Url::current([0 => 'gallery-product/index', 'parent_id' => $model->id]), [
                                'class' => 'btn btn-default btn-block btn-social',
                    ]);
                },
                'filter' => false,
            ],
        ],
    ]);
    ?>
</div>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['category/index']), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>