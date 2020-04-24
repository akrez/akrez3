<?php

use yii\helpers\Html;
use app\assets\AdminAsset;
use app\widgets\Alert;

AdminAsset::register($this);
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

    <body class="hold-transition skin-green-light sidebar-mini">
        <?php $this->beginBody() ?>

        <div class="wrapper">

            <!-- Main Header -->
            <header class="main-header">

                <!-- Logo -->
                <?= $this->render('logo') ?>

                <!-- Header Navbar -->
                <?= $this->render('navbar') ?>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <?= $this->render('left'); ?>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->


                <div class="col-xs-12">
                    <br />
                    <?= Alert::widget() ?>
                </div>

                <!-- Main content -->
                <section class="content">
                    <?= $content ?>
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->



            <?= $this->render('footer') ?>

            <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
            <div class="control-sidebar-bg"></div>
        </div>

        <?php $this->endBody() ?>
    </body>

</html>
<?php $this->endPage() ?>
