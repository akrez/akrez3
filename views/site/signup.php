<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="site-index">
    <div class="container">
        <div class="row">
            <div class="col-sm-4 col-xs-12">
            </div>
            <div class="col-sm-4 col-xs-12">
                <h3 style="margin-bottom: 20px;"><?= Yii::t('app', 'Signup') ?></h3>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'login-form',
                            'fieldConfig' => [
                                'template' => '<div class="input-group">{label}{input}</div>{error}',
                                'labelOptions' => [
                                    'class' => 'input-group-addon',
                                ],
                            ]
                ]);
                ?>
                <?= $form->field($model, 'email')->textInput() ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block" style="float: right;"> <?= Yii::t('app', 'Signup') ?> </button>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
            <div class="col-sm-4 col-xs-12">
            </div>
        </div>
    </div>
</div>
