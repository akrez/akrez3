<?php

use yii\helpers\Url;
use yii\helpers\Html;
?>

<nav class="navbar navbar-static-top" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
    </a>
    <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <li class="dropdown messages-menu">
                <?= Html::a('<span>' . Yii::t('app', 'Blogs') . '</span>', Url::toRoute(['site/blogs'])) ?>
            </li>
            <li class="dropdown messages-menu">
                <?= Html::a('<span>' . Yii::t('app', 'Signout') . '</span>', Url::toRoute(['/site/signout'])) ?>
            </li>
        </ul>
    </div>
</nav>