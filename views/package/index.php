<?php

use app\components\AdminHelper;
use app\models\Color;
use app\models\Status;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\grid\GridView;

$this->title = Yii::t('app', 'Packages') . ': ' . $parentModel->title;

array_unshift($garanties, 'گارانتی سلامت و اصالت');
$garanties = array_combine($garanties, $garanties);

$format = <<< SCRIPT
function format(data, container) {
    return ' <span style="background-color: #' + data.id + ';">⠀⠀</span> ' + data.text;
}
SCRIPT;
$this->registerJs($format, View::POS_HEAD);
?>

<h1><?= Html::encode($this->title) ?></h1>

<?=
$this->render('_form', [
    'model' => $newModel,
    'garanties' => $garanties,
]);
?>

<div class="table-responsive">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
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
            'price:price',
            'guaranty',
            [
                'attribute' => 'color',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    if ($model->color) {
                        return ' <span style="background-color: #' . $model->color . ';">⠀⠀</span> ' . Color::getLabel($model->color);
                    }
                    return '';
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'color',
                    'data' => Color::getList(),
                    'options' => [
                        'placeholder' => $searchModel->getAttributeLabel('color'),
                        'id' => Html::getInputId($searchModel, 'color') . '-' . $searchModel->id,
                        'dir' => 'rtl',
                    ],
                    'pluginOptions' => [
                        'templateResult' => new JsExpression('format'),
                        'templateSelection' => new JsExpression('format'),
                        'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                        'allowClear' => true
                    ],
                ]),
            ],
            'des',
            [
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    if ($model->status == Status::STATUS_ACTIVE) {
                        return Html::a(' <span class="glyphicon glyphicon-ban-circle"></span> ' . Status::getLabel(Status::STATUS_DISABLE), Url::current([0 => 'package/status', 'id' => $model->id, 'status' => Status::STATUS_DISABLE]), ['class' => 'btn btn-warning btn-block btn-social']);
                    }
                    return Html::a(' <span class="glyphicon glyphicon-ok-circle"></span> ' . Status::getLabel(Status::STATUS_ACTIVE), Url::current([0 => 'package/status', 'id' => $model->id, 'status' => Status::STATUS_ACTIVE]), ['class' => 'btn btn-primary btn-block btn-social']);
                },
            ],
            [
                'format' => 'raw',
                'value' => function ($model, $key, $index, $grid) {
                    return Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), Url::current([0 => 'package/remove', 'id' => $model->id]), [
                                'class' => 'btn btn-danger btn-block btn-social',
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);
                },
            ],
        ],
    ]);
    ?>
</div>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['product/index', 'parent_id' => $parentModel->category_id]), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>