<?php

use app\components\AdminHelper;
use yii\helpers\Html;

$model = Yii::$app->blog->getIdentity();
$name = Html::encode($model->name);
?>
<a href="<?= AdminHelper::url(['default/index']); ?>" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b><?= strtoupper(substr($name, 0, 1)) ?></b></span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b><?= Html::encode($model->title) ?></b></span>
</a>
