<?php

use yii\helpers\Url;
?>

<?= $user->getAttributeLabel('email') ?>: <?= $user->email ?>
<br>
<?= $user->getAttributeLabel('reset_token') ?>: <?= $user->reset_token ?>
<br>
<a href="<?= Url::to(['site/reset-password', 'email' => $user->email, 'reset_token' => $user->reset_token], true) ?>"><?= Yii::t('app', 'ResetPassword') ?></a>