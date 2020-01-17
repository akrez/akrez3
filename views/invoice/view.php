<?php

use app\components\AdminHelper;
use yii\helpers\Html;
use app\models\Invoice;
use app\models\Province;
use yii\helpers\HtmlPurifier;

$this->title = Yii::t('app', 'Basket') . ': ' . $invoice->id;

?>

<h1 class="pb20"><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-sm-12 pb20">
            <?=
            $this->render('_basket_table', [
                'list' => $list,
            ])
            ?>
        </div>
    </div>

<div class="row">
    <div class="col-sm-12 pb20">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td class="white-background"><?= $invoice->getAttributeLabel('id') ?></td><td><?= $invoice->id ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('created_at') ?></td><td><?= Yii::$app->formatter->asDatetimefa($invoice->created_at) ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('updated_at') ?></td><td><?= Yii::$app->formatter->asDatetimefa($invoice->updated_at) ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('status') ?></td><td><?= Invoice::statuseLabel($invoice->status) ?></td>
                </tr>
                <tr>
                    <td class="white-background"><?= $invoice->getAttributeLabel('name') ?></td><td><?= HtmlPurifier::process($invoice->name) ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('phone') ?></td><td><?= HtmlPurifier::process($invoice->phone) ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('mobile') ?></td><td><?= HtmlPurifier::process($invoice->mobile) ?></td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td class="white-background"><?= $invoice->getAttributeLabel('province') ?></td><td><?= Province::getLabel($invoice->province) ?></td>
                    <td class="white-background"><?= $invoice->getAttributeLabel('address') ?></td><td colspan="5"><?= HtmlPurifier::process($invoice->address) ?></td>
                </tr>
                <tr>
                    <td class="white-background"><?= $invoice->getAttributeLabel('des') ?></td><td colspan="7"><?= HtmlPurifier::process($invoice->des) ?></td>
                </tr>
                <tr>
                    <td class="white-background"><?= $invoice->getAttributeLabel('price') ?></td><td colspan="7"><?= HtmlPurifier::process(Yii::$app->formatter->asPrice($invoice->price)) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row pb20 pt20">
    <div class="col-sm-2">
        <?php if ($invoice->status == Invoice::STATUS_UNVERIFIED): ?>
            <a type="button" class="btn btn-success btn-block" style="margin-top: 20px;" href="<?= AdminHelper::url(['invoice/verify', 'id' => $invoice->id]) ?>"><?= Yii::t('app', 'Verify') ?></a>
        <?php endif; ?>
    </div>
    <div class="col-sm-8">
    </div>
    <div class="col-sm-2">
        <?= Html::a(' <span class="glyphicon glyphicon-share-alt"></span> ' . Yii::t('app', 'Back'), AdminHelper::url(['invoice/index']), ['class' => 'btn btn-default btn-block btn-social']) ?>
    </div>
</div>