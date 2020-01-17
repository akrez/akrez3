<?php

use app\assets\SiteAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;

SiteAsset::register($this);
$this->title = ($this->title ? $this->title : Yii::$app->name);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Yii::$app->view->registerLinkTag(['rel' => 'icon', 'href' => Yii::getAlias('@web/cdn/favicon.png')]) ?>
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>

    <body>
        <?php $this->beginBody() ?>

        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-default navbar-static-top',
            ],
        ]);

        $menuItems = [];
        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => Yii::t('app', 'Signup'), 'url' => Url::toRoute(['/site/signup'])];
            $menuItems[] = ['label' => Yii::t('app', 'Signin'), 'url' => Url::toRoute(['/site/signin'])];
        } else {
            $menuItems[] = ['label' => Yii::t('app', 'Blogs'), 'url' => Url::toRoute(['/site/blogs'])];
            $menuItems[] = ['label' => Yii::t('app', 'Signout'), 'url' => Url::toRoute(['/site/signout'])];
        }
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => $menuItems,
        ]);

        NavBar::end();
        ?>

        <div class="container">

            <div class="row">
                <div class="col-sm-12">
                    <?= Alert::widget() ?>
                    <?= $content ?>
                </div>
            </div>

        </div>
        <?php $this->endBody() ?>
    </body>

</html>
<?php $this->endPage() ?>
