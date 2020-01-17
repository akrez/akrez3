<?php

use app\components\AdminHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Garanties') . ': ' . $model->title;

?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<?php
$form = ActiveForm::begin([
            'action' => Url::current(['id' => $model->id]),
            'fieldConfig' => [
                'template' => '<div class="input-group">{label}{input}</div>{error}',
                'labelOptions' => ['class' => 'input-group-addon'],
            ],
        ]);

?>

<div class="row">
    <?php
    $model->garanties = str_ireplace(",", "\n", $model->garanties);
    echo $form->field($model, 'garanties', ['template' => '<div class="input-group">{label}{input}</div>{hint}{error}', 'options' => ['class' => 'col-sm-12']])->textarea(['rows' => 12])->hint('گزینه‌ها را بوسیله ویرگول (,) یا خط جدید از هم جدا کنید.');

    ?>
</div>

<div class="row pb20">
    <div class="col-sm-2">
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-block' : 'btn btn-primary btn-block']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' .Yii::t('app', 'Back'), AdminHelper::url(['category/index']), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>