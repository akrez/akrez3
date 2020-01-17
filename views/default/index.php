<?php

use app\components\AdminHelper;
use yii\helpers\Html;

$this->title = Yii::$app->blog->getIdentity()->title;
$model = Yii::$app->blog->getIdentity();
?>

<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-2 pb20">
                <a href="<?= AdminHelper::url(['gallery-logo/index']) ?>">
                    <?= Html::img(($model->logo ? AdminHelper::getImageUrl('logo', '400', $model->logo) : '@web/cdn/image/logo.png'), ['class' => 'img img-responsive', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
                </a>
            </div>
            <div class="col-sm-10 pb20">
                <?= Html::tag('h3', Html::encode($model->title), ['style' => 'margin-top: 0;']) ?>
                <?= ($model->slug ? Html::tag('h4', Html::encode($model->slug)) : '') ?>
                <?= ($model->des ? Html::tag('p', Html::encode($model->des), ['style' => 'text-align: justify;']) : '') ?>
            </div>
        </div>
    </div>
</div>
