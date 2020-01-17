<?php

use app\components\AdminHelper;
use app\models\Invoice;
use app\models\Province;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Invoices');
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="table-responsive">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'status',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'data' => Invoice::statuses(),
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => '',
                        'id' => Html::getInputId($searchModel, 'status') . '-' . $searchModel->id,
                        'dir' => 'rtl',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'value' => function ($model, $key, $index, $grid) {
                    return Invoice::statuseLabel($model->status);
                },
            ],
            'updated_at:datetimefa',
            'name',
            [
                'attribute' => 'province',
                'value' => function ($model, $key, $index, $grid) {
                    return Province::getLabel($model->province);
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'province',
                    'data' => Province::getList(),
                    'options' => [
                        'placeholder' => '',
                        'id' => Html::getInputId($searchModel, 'province') . '-' . $searchModel->id,
                        'dir' => 'rtl',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'mobile',
            'phone',
            'price:price',
            [
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    if (in_array($model->status, [Invoice::STATUS_VERIFIED, Invoice::STATUS_UNVERIFIED])) {
                        return '<a class="btn btn-danger btn-block btn-social" style="height: 34px;" href="' . AdminHelper::url(['invoice/remove', 'id' => $model->id]) . '" data-confirm="' . Yii::t('yii', 'Are you sure you want to delete this item?') . '">' . ' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('yii', 'Delete') . '</a>';
                    }
                    return '';
                },
            ],
            [
                'label' => '',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    return '<a class="btn btn-default btn-block btn-social" style="height: 34px;" href="' . AdminHelper::url(['invoice/view', 'id' => $model->id]) . '" >' . ' <span class="glyphicon glyphicon-shopping-cart"></span> ' . Yii::t('app', 'Basket') . '</a>';
                },
            ],
        ],
    ]);
    ?>

</div>