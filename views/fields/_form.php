<?php

use app\components\AdminHelper;
use app\models\FieldList;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\widgets\ActiveForm;

$categoryFieldsTitle = ArrayHelper::map($categoryFields, 'id', 'title');

$boxSize = 'col-sm-4 col-md-3 col-lg-3';

$form = ActiveForm::begin([
        'action' => AdminHelper::url(['fields/index', 'parent_id' => $parentModel->id]),
        'fieldConfig' => [
            'hintOptions' => ['class' => 'input-group-addon'],
            'labelOptions' => ['class' => 'input-group-addon'],
            'options' => ['class' => $boxSize,]
        ],
        'enableClientValidation' => ($state == 'update' ? true : false),
    ]);

?>

<?php
foreach ($models as $model):

    $id = ($state == 'update' ? $model->id : $model->field_id);
    $uniqueUiId = '-' . $state . '-' . $model->tableName() . '-' . $id;
    $namePrefix = "[" . $id . "]";

    ?>

    <div class="row">

        <?php
        echo $form->field($model, "{$namePrefix}field_id")->widget(Select2::classname(), [
            'data' => $categoryFieldsTitle,
            'hideSearch' => true,
            'options' => [
                'placeholder' => $model->getAttributeLabel('field_id'),
                'id' => Html::getInputId($model, 'field_id') . $uniqueUiId,
                'dir' => 'rtl',
                'disabled' => 'disabled'
            ],
            'pluginOptions' => [
                'allowClear' => false
            ],
        ])->label(false);

        ?>

        <div class="col-sm-4 col-md-6 col-lg-6">
            <div class="row">
                <?php
                $fieldtype = $model->getFieldAttribute('type');
                if (in_array($fieldtype, [FieldList::TYPE_STRING, FieldList::TYPE_NUMBER,])) {
                    $unit = $model->getFieldAttribute('unit');
                    if ($fieldtype == FieldList::TYPE_NUMBER && $model->value != '') {
                        $model->value = floatval($model->value);
                    }
                    echo $form->field($model, "{$namePrefix}value", ['template' => ($unit ? '<div class="input-group">{label}{input}{hint}</div>{error}' : "{label}\n{input}\n{hint}\n{error}"), 'options' => ['class' => 'col-xs-12',]])->widget(AutoComplete::classname(), [
                        'options' => [
                            'class' => 'form-control',
                            'id' => Html::getInputId($model, 'value') . $uniqueUiId,
                        ],
                        'clientOptions' => [
                            'minLength' => 0,
                            'source' => (empty($model->getFieldAttribute('options')) ? [] : explode(',', $model->getFieldAttribute('options'))),
                        ],
                    ])->label(false)->hint($unit ? Html::encode($unit) : false);
                } elseif (in_array($fieldtype, [FieldList::TYPE_BOOLEAN,])) {
                    echo $form->field($model, "{$namePrefix}value", ['options' => ['class' => 'col-sm-6 col-md-4',]])->widget(Select2::classname(), [
                        'data' => [
                            0 => ($model->getFieldAttribute('label_no') ? $model->getFieldAttribute('label_no') : Yii::$app->formatter->booleanFormat[0]),
                            1 => ($model->getFieldAttribute('label_yes') ? $model->getFieldAttribute('label_yes') : Yii::$app->formatter->booleanFormat[1]),
                        ],
                        'hideSearch' => true,
                        'options' => [
                            'placeholder' => '',
                            'id' => Html::getInputId($model, 'value') . $uniqueUiId,
                            'dir' => 'rtl',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label(false);
                }

                ?>
            </div>
        </div>

        <div class="<?= $boxSize ?>">
            <?php if ($state == 'update'): ?>
                <div class="form-group">
                    <?=
                    Html::a(Yii::t('app', 'Remove'), AdminHelper::url([0 => 'fields/delete', 'id' => $model->id, 'parent_id' => $parentModel->id, 'form_name' => $model->formName()]), [
                        'class' => 'btn btn-danger btn-block',
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                    ]);

                    ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

<?php endforeach; ?>

<div class="row pb20">
    <div class="<?= $boxSize ?>">
        <?php if ($state == 'update') : ?>
            <input type="submit" class="btn btn-primary btn-block" name="<?= $state ?>" value="<?= Yii::t('app', 'Update') ?>" />
        <?php else : ?>
            <input type="submit" class="btn btn-success btn-block" name="<?= $state ?>" value="<?= Yii::t('app', 'Add') ?>" />
        <?php endif; ?>
    </div>
</div>

<?php ActiveForm::end(); ?>