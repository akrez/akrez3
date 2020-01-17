<?php

use app\components\AdminHelper;
use app\models\Color;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;

$models = [];
foreach ($list['baskets'] as $basket) {
    $package = $list['packages'][$basket['package_id']];
    $product = $list['products'][$package['product_id']];
    //
    $model['image'] = $product['image'];
    $model['title'] = $product['title'];
    $model['cnt'] = $basket['cnt'];
    $model['price'] = $package['price'];
    $model['color'] = $package['color'];
    $model['guaranty'] = $package['guaranty'];
    $model['des'] = $package['des'];
    $models[] = $model;
}

$dataProvider = new ArrayDataProvider([
    'allModels' => $models,
    'modelClass' => 'app\models\Model',
    'sort' => false,
    'pagination' => false,
        ]);
?>

<div class="table-responsive">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'image',
                'value' => function ($model, $key, $index, $grid) {
                    return ($model['image'] ? Html::img(AdminHelper::getImageUrl('product', '_33_66', $model['image'])) : '');
                },
                'format' => 'raw',
            ],
            'title',
            [
                'attribute' => 'color',
                'value' => function ($model, $key, $index, $grid) {
                    if ($model['color']) {
                        return ' <span style="background-color: #' . $model['color'] . ';">⠀⠀</span> ' . Color::getLabel($model['color']);
                    }
                    return '';
                },
                'format' => 'raw',
            ],
            'guaranty',
            'des',
            'price:price',
            'cnt',
        ],
    ]);
    ?>
</div>
