<?php

use app\components\AdminHelper;
use app\models\FieldList;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->registerCss('.select2-container--krajee .select2-selection { border-bottom-right-radius: 0; border-top-right-radius: 0; }');

$this->title = Yii::t('app', 'Fields') . ': ' . $parentModel->title;
$models = array_merge([$newModel], $dataProvider->getModels());
?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<?php foreach ($models as $model): ?>

    <?php
    $form = ActiveForm::begin([
                'action' => Url::current(['id' => $model->id]),
                'fieldConfig' => [
                    'template' => '<div class="input-group">{label}{input}</div>{error}',
                    'labelOptions' => ['class' => 'input-group-addon'],
                    'options' => ['class' => 'col-sm-4 col-lg-3',]
                ],
    ]);
    $model->options = str_ireplace(",", " , ", $model->options);
    ?>

    <h4 class=""><?= $model->isNewRecord ? '' : Html::encode($model->title) ?></h4>

    <div class="row">

        <?=
        $form->field($model, 'type')->widget(Select2::classname(), [
            'data' => FieldList::typeList(),
            'hideSearch' => true,
            'options' => ($model->isNewRecord ? [] : ['disabled' => 'disabled']) + [
        'placeholder' => $model->getAttributeLabel('type'),
        'id' => Html::getInputId($model, 'type') . '-' . $model->id,
        'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
        ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'unit')->textInput(['maxlength' => true]); ?>
    </div>

    <div class="row">
        <?= $form->field($model, 'seq')->textInput(['type' => 'number']); ?>
        <?=
        $form->field($model, 'in_summary')->widget(Select2::classname(), [
            'data' => [
                1 => Yii::$app->formatter->booleanFormat[1],
                0 => Yii::$app->formatter->booleanFormat[0],
            ],
            'hideSearch' => true,
            'options' => [
                'placeholder' => $model->getAttributeLabel('in_summary'),
                'id' => Html::getInputId($model, 'in_summary') . '-' . $model->id,
                'dir' => 'rtl',
            ],
            'pluginOptions' => [
                'allowClear' => false
            ],
        ]);
        ?>
    </div>

    <?php if ($model->type == FieldList::TYPE_BOOLEAN) : ?>
        <div class="row">
            <?= $form->field($model, 'label_no')->textInput(); ?>
            <?= $form->field($model, 'label_yes')->textInput(); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php
        if (!$model->isNewRecord) {
            echo $form->field($model, 'widgets', [
                'template' => '{label}{input}{hint}{error}',
                'labelOptions' => ['class' => ''], 'options' => ['class' => 'col-sm-12 col-lg-9']
            ])->checkboxList(FieldList::getTypeWidgets($model->type));
        }
        ?>
    </div>


    <div class="row pb20">
        <div class="col-sm-4 col-lg-2">
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-block' : 'btn btn-primary btn-block']) ?>
            </div>
        </div>
        <div class="col-sm-4 col-lg-5">
        </div>
        <div class="col-sm-4 col-lg-2">

            <div class="form-group">
                <?php
                if (!$model->isNewRecord) {
                    echo Html::a(Yii::t('app', 'Remove'), AdminHelper::url(['field/delete', 'id' => $model->id, 'parent_id' => $parentModel->id]), [
                        'class' => 'btn btn-danger btn-block',
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);
                }
                ?>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

<?php endforeach; ?>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['category/index']), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>