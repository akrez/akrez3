<?php

use app\components\AdminHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'LogoGalleries');

$this->registerCss("

    .row.equal {
        display: flex;
        flex-wrap: wrap;
    }

    .btn-block {
        margin-top: 5px;
    }

    a.thumbnail {
        text-decoration: none;
    }

    .thumbnail img {
        text-decoration: none;
    }

");

$models = $dataProvider->getModels();

$gridSize = "col-sm-6 col-md-4 col-lg-3";
?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<div class="row pb20">
    <div class="<?= $gridSize ?>">
        <?= Html::a(' <span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'UploadNewImage'), 'javascript:void(0);', ['class' => 'btn btn-success btn-block btn-social', "onclick" => "$('#gallery-subbutton').click()"]); ?>
    </div>
</div>

<?php if ($models) : ?>
    <div class="row pb20 equal">
        <?php foreach ($models as $model) : ?>
            <div class="pt20 <?= $gridSize ?>">
                <?= Html::img(AdminHelper::getImageUrl('logo', '400', $model->name), ['class' => 'img img-responsive img-rounded', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
                <?php
                if ($parentModel->logo != $model->name) {
                    echo Html::a(' <span class="glyphicon glyphicon-star"></span> ' . Yii::t('app', 'SelectDefaultImage'), AdminHelper::url([0 => 'gallery-logo/default', 'name' => $model->name]), ['class' => 'btn btn-primary btn-social btn-block frame-button',]);
                }
                echo Html::a(' <span class="glyphicon glyphicon-trash"></span> ' . Yii::t('yii', 'Delete'), AdminHelper::url([0 => 'gallery-logo/delete', 'name' => $model->name]), [
                    'class' => 'btn btn-danger btn-social btn-block frame-button',
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                ]);
                ?>
            </div>
        <?php endforeach ?>
    </div>
<?php else: ?>
    <div class="row equal">
        <div class="pt20 col-xs-12">
            <p>
                <?= Yii::t('yii', 'No results found.'); ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="row pb20 pt20">
    <div class="<?= $gridSize ?>">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['default/index']), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>

<?php
$form = ActiveForm::begin(['action' => AdminHelper::url(['gallery-logo/upload']), 'options' => ['enctype' => 'multipart/form-data', 'id' => 'gallery-form']]);
echo $form->field($newModel, 'image', ['options' => ['style' => 'display: none']])->fileInput(['id' => 'gallery-subbutton', 'onchange' => "this.form.submit()"]);
ActiveForm::end();
?> 
