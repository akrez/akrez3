<?php

use app\components\AdminHelper;
?>
<ul class="sidebar-menu">
    <li><a href="<?= AdminHelper::url(['category/index']) ?>"><i class="glyphicon glyphicon-list"></i><span><?= Yii::t('app', 'Categories') ?></span></a></li>
    <li><a href="<?= AdminHelper::url(['invoice/index']) ?>"><i class="glyphicon glyphicon-list-alt"></i><span><?= Yii::t('app', 'Invoices') ?></span></a></li>
    <li class="treeview">
        <a href="#"><i class="glyphicon glyphicon-wrench"></i><span><?= 'تنظیمات' . ' ' . Yii::t('app', 'Blog') ?></span></a>        
        <ul class="treeview-menu" style="right: 49px;">
            <li><a href="<?= AdminHelper::url(['default/update']) ?>"> <?= Yii::t('app', 'Update {name}', ['name' => Yii::t('app', 'Blog')]) ?> </a></li>
            <li><a href="<?= AdminHelper::url(['gallery-logo/index']) ?>"> <?= Yii::t('app', 'LogoGalleries') ?> </a></li>
        </ul>    
    </li>
</ul>