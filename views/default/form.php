<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\Status;
use yii\helpers\Url;

if ($model->isNewRecord) {
    $this->title = Yii::t('app', 'Create {name}', ['name' => Yii::t('app', 'Blog')]);
} else {
    $this->title = Yii::t('app', 'Update {name}', ['name' => Yii::t('app', 'Blog')]);
}
?>

<div class="blog-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => '<div class="input-group">{label}{input}</div>{error}',
                    'labelOptions' => ['class' => 'input-group-addon'],
                    'options' => ['class' => 'col-sm-4',]
                ]
    ]);
    ?>

    <div class="row">
        <?= $form->field($model, 'name')->textInput(['placeholder' => 'irankhodro', 'maxlength' => true] + ($model->isNewrecord ? [] : ['disabled' => true])) ?>
        <?= $form->field($model, 'title')->textInput(['placeholder' => 'ایران خودرو', 'maxlength' => true]) ?>
        <?php
        $model->status = ($model->status == null ? Status::STATUS_DISABLE : $model->status);
        echo $form->field($model, 'status', ['template' => "{input}\n{hint}\n{error}"])->widget(Select2::classname(), [
            'data' => Status::getDefaults(),
            'hideSearch' => true,
            'options' => [
                'placeholder' => $model->getAttributeLabel('status'),
                'id' => Html::getInputId($model, 'status') . '-' . $model->name,
                'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'slug', ['options' => ['class' => 'col-sm-12',]])->textInput(['placeholder' => 'راه تو را می خواند', 'maxlength' => true]) ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'des', ['options' => ['class' => 'col-sm-12',]])->textarea() ?>
    </div>

    <div class="row">
        <div class="col-sm-2">
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-block' : 'btn btn-primary btn-block']) ?>
            </div>
        </div>
        <div class="col-sm-6">
        </div>
        <div class="col-sm-2">
            <?php if (!$model->isNewRecord): ?>
                <div class="form-group">
                    <?=
                    Html::a(Yii::t('app', 'Remove'), Url::to(['default/delete', '_blog' => $model->name]), [
                        'class' => 'btn btn-danger btn-block',
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), ($model->isNewRecord ? Url::to(['site/blogs']) : Url::to(['default/index', '_blog' => $model->name])), ['class' => 'btn btn-default btn-block btn-social']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
