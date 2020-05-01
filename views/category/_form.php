<?php

use app\components\AdminHelper;
use app\models\Category;
use app\models\Status;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Category */
/* @var $form ActiveForm */
?>

<?php
$form = ActiveForm::begin([
            'fieldConfig' => [
                'template' => '<div class="input-group">{label}{input}</div>{error}',
                'labelOptions' => [
                    'class' => 'input-group-addon',
                ],
            ]
        ]);
?>

<div class="row">
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'status')->dropDownList(Status::getDefaults()) ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12">
        <?= $form->field($model, 'des')->textInput() ?>
    </div>
</div>

<div class="row pb20">
    <div class="col-xs-12 col-sm-2">
        <?php
        echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => 'btn btn-block ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')]);
        ?>
    </div>
    <div class="col-xs-12 col-sm-6">
    </div>
    <div class="col-xs-12 col-sm-2">
        <?php
        if (!$model->isNewRecord) {
            echo Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'Remove'), AdminHelper::url([0 => 'category/remove', 'id' => $model->id]), [
                'class' => 'btn btn-danger btn-block btn-social',
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            ]);
        }
        ?>
    </div>
    <div class="col-xs-12 col-sm-2">
        <?php
        if (!$model->isNewRecord) {
            echo Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['index']), ['class' => 'btn btn-block btn-default btn-social']);
        }
        ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
