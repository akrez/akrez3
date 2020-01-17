<?php

use app\components\AdminHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'ProductFields') . ': ' . $parentModel->title;
?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

<div class="row">
    <?php if ($parentModel->image): ?>
        <div class="col-sm-3">
            <?php echo Html::img(AdminHelper::getImageUrl('product', '400', $parentModel->image), ['class' => 'img img-responsive img-rounded', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
        </div>
    <?php endif; ?>
    <div class="col-sm-9">
        <?php
        $isEmpty = true;
        foreach ($models as $state => $groupModels) :
            if (count($groupModels) > 0) :
                $isEmpty = false;
                echo $this->render('_form', [
                    'state' => $state,
                    'models' => $groupModels,
                    'parentModel' => $parentModel,
                    'categoryFields' => $categoryFields,
                ]);
            endif;
        endforeach;
        ?>
        <?php if ($isEmpty) : ?>
            <div class="row">
                <div class="col-xs-12">
                    <p>
                        <?= Yii::t('yii', 'No results found.'); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <div class="row pt20 pb20">
            <div class="col-sm-4 col-md-3 col-lg-3">
                <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['product/index', 'parent_id' => $parentModel->category_id]), ['class' => 'btn btn-default btn-block btn-social']) ?>
            </div>
        </div>
    </div>
</div>