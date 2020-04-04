<?php

use app\components\AdminHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'ProductFields') . ': ' . $parentModel->title;
?>

<div class="row pb20">
    <div class="col-sm-12">
        <h1 style="margin-top: 0;"><?= Html::encode($this->title) ?></h1>
    </div>
</div>

<div class="row">

    <?php if ($parentModel->image): ?>
        <div class="col-sm-3">
            <div class="row pb20">
                <div class="col-sm-12">
                    <?php echo Html::img(AdminHelper::getImageUrl('product', '400', $parentModel->image), ['class' => 'img img-responsive img-rounded', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-sm-9">

        <?php
        if (count($models['update'])):
            echo $this->render('_form', [
                'state' => 'update',
                'models' => $models['update'],
                'parentModel' => $parentModel,
                'categoryFields' => $categoryFields,
            ]);
            echo '<div class="row"> <div class="col-sm-12"> <hr style="margin-top: 5px;"> </div> </div>';
        endif;
        ?>

        <?php
        if (count($models['create'])):
            echo $this->render('_form', [
                'state' => 'create',
                'models' => $models['create'],
                'parentModel' => $parentModel,
                'categoryFields' => $categoryFields,
            ]);
        else:
            echo '<div class="row pb20"> <div class="col-xs-12"> <p> ' . Yii::t('yii', 'No results found.') . ' </p> </div> </div>';
        endif;
        ?>

        <div class="row pb20">
            <div class="col-sm-4 col-md-3 col-lg-3">
                <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['product/index', 'parent_id' => $parentModel->category_id]), ['class' => 'btn btn-default btn-block btn-social']) ?>
            </div>
        </div>

    </div>
</div>