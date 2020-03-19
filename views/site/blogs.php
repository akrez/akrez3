<?php

use app\components\AdminHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Blogs');
?>

<div class="row">
    <div class="col-sm-12">
        <?php if ($models) { ?>
            <?php foreach ($models as $model) { ?>
                <div class="row">
                    <div class="col-sm-2 pb20">
                        <?= Html::img(($model->logo ? AdminHelper::getImageUrl('logo', '400', $model->logo) : '@web/cdn/image/logo.png'), ['class' => 'img img-responsive img-rounded', 'style' => 'margin-left: auto; margin-right: auto;']); ?>
                    </div>
                    <div class="col-sm-10 pb20">
                        <?= Html::tag('h3', Html::encode($model->title), ['style' => 'margin-top: 0;']) ?>
                        <?= ($model->slug ? Html::tag('h4', Html::encode($model->slug)) : '') ?>
                        <?= ($model->des ? Html::tag('p', Html::encode($model->des), ['style' => 'text-align: justify;']) : '') ?>
                        <?= Html::a(' <span class="glyphicon glyphicon-road"></span> ' . 'ورود به پنل مدیریت', Url::to(['default/index', '_blog' => $model->name]), ['class' => 'btn btn-default btn-social']); ?>
                        <?= Html::a(' <span class="glyphicon glyphicon-eye-open"></span> ' . 'نمایش', "http://".$model->name.".akrezing.ir/", ['class' => 'btn btn-default btn-social', 'target' => '_blank']); ?>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="row">
                <div class="col-sm-2">
                    <?= Yii::t('yii', 'No results found.'); ?>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-sm-2 pb20">
                <a type="button" class="btn btn-success btn-block" style="margin-top: 20px;" href="<?= Url::to(['site/create']) ?>"><?= Yii::t('app', 'Create {name}', ['name' => Yii::t('app', 'Blog')]) ?></a>
            </div>
        </div>
    </div>
</div>
