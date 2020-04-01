<?php

use app\models\Color;
use app\models\Status;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
$this->registerCss('
.select2-container--krajee .select2-selection {
    border-radius: 4px 0 0 4px;
}
');
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
        <?=
		$form->field($model, 'guaranty')->widget(Select2::classname(), [
            'data' => $garanties,
            'options' => [
                'placeholder' => '',
                'id' => Html::getInputId($model, 'guaranty') . '-' . $model->id,
                'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => true,
                'createTag' => new JsExpression("function (params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    }
                }"),
            ],
        ]);
        $this->registerCss('.' . 'select2-search__field' . ' { background: none !important; } ');
        ?>
    </div>
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'price')->textInput() ?>
    </div>
    <div class="col-xs-12 col-sm-4">
        <?= $form->field($model, 'status')->dropDownList(Status::getDefaults()) ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-4">
        <?=
        $form->field($model, 'color')->widget(Select2::classname(), [
            'data' => Color::getList(),
            'options' => [
                'placeholder' => '',
                'id' => Html::getInputId($model, 'color') . '-' . $model->id,
                'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'templateResult' => new JsExpression('format'),
                'templateSelection' => new JsExpression('format'),
                'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                'allowClear' => true
            ],
        ]);
        ?>

    </div>
    <div class="col-xs-12 col-sm-8">
        <?= $form->field($model, 'des')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row pb20">
    <div class="col-xs-12 col-sm-2">
        <?php
        echo Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-block btn-success']);
        ?>
    </div>
    <div class="col-xs-12 col-sm-8">
    </div>
    <div class="col-xs-12 col-sm-2">
    </div>
</div>

<?php ActiveForm::end(); ?>