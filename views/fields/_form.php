<?php

use app\components\AdminHelper;
use app\models\FieldList;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\widgets\ActiveForm;

$boxSizeNormal = 'col-xs-12 col-sm-4 col-md-3 col-lg-3';
$categoryFieldsTitle = ArrayHelper::map($categoryFields, 'id', 'title');

$form = ActiveForm::begin([
            'action' => AdminHelper::url(['fields/index', 'parent_id' => $parentModel->id]),
            'fieldConfig' => [
                'hintOptions' => ['class' => 'input-group-addon'],
                'labelOptions' => ['class' => 'input-group-addon'],
            ],
            'enableClientValidation' => false,
        ]);
foreach ($models as $model):
    $id = ($state == 'update' ? $model->id : $model->field_id);
    $uniqueUiId = '-' . $state . '-' . $model->tableName() . '-' . $id;
    $namePrefix = "[" . $id . "]";
    ?>

    <div class="row">
        <?php
        $field = $categoryFields[$model->field_id];
        $fieldtype = $field['type'];
        $inputOptions = [
            'id' => Html::getInputId($model, 'value') . $uniqueUiId,
            'placeholder' => '',
            'class' => 'form-control',
            'dir' => 'rtl',
        ];
        if ($state == 'update') {
            $inputOptions['disabled'] = 'disabled';
        }
        if (in_array($fieldtype, [FieldList::TYPE_STRING, FieldList::TYPE_NUMBER,])) {
            $unit = $field['unit'];
            echo $form->field($model, "{$namePrefix}value", ['template' => '<div class="input-group">{label}{input}{hint}</div>{error}', 'options' => ['class' => 'col-xs-12 col-sm-8 col-md-9 col-lg-9',]])->widget(AutoComplete::classname(), [
                'options' => $inputOptions,
                'clientOptions' => [
                    'minLength' => 0,
                    'source' => array_filter(explode(',', $field['options'])),
                ],
            ])->label(isset($categoryFieldsTitle[$model->field_id]) ? Html::encode($categoryFieldsTitle[$model->field_id]) : false)->hint($unit ? Html::encode($unit) : false);
        } elseif (in_array($fieldtype, [FieldList::TYPE_BOOLEAN,])) {
            echo $form->field($model, "{$namePrefix}value", ['template' => '<div class="input-group">{label}{input}{hint}</div>{error}', 'options' => ['class' => 'col-xs-12 col-sm-8 col-md-9 col-lg-9',]])->widget(Select2::classname(), [
                'data' => [
                    0 => ($field['label_no'] ? $field['label_no'] : Yii::$app->formatter->booleanFormat[0]),
                    1 => ($field['label_yes'] ? $field['label_yes'] : Yii::$app->formatter->booleanFormat[1]),
                ],
                'hideSearch' => true,
                'options' => $inputOptions,
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label(isset($categoryFieldsTitle[$model->field_id]) ? Html::encode($categoryFieldsTitle[$model->field_id]) : false);
        }
        ?>

        <div class="<?= $boxSizeNormal ?>">
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

    <?php
endforeach;
?>

<?php if ($state == 'create') : ?>
    <div class="row pb20">
        <div class="<?= $boxSizeNormal ?>">
            <input type="submit" class="btn btn-success btn-block" name="<?= $state ?>" value="<?= Yii::t('app', 'Add') ?>" />
        </div>
    </div>
<?php endif; ?>

<?php
ActiveForm::end();
