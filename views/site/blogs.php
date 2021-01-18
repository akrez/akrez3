<?php

use app\components\AdminHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Blogs');

$this->registerCss("
    .row.equal {
        display: flex;
        flex-wrap: wrap;
    }

    .thumbnail {
        margin: 0px;
        border-radius: 0;
        padding-left: 5px;
        padding-right: 5px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    a.thumbnail {
        text-decoration: none;
        color: #777;
    }

    a.thumbnail:focus , a.thumbnail:hover {
        text-decoration: none;
        color: #333;
    }

    .thumbnail * {
        text-align: center;
        margin: 9px 0 0;
        padding: 10px;
    }
    
    .thumbnail img {
        text-decoration: none;
        margin-left: auto; 
        margin-right: auto;
        margin-top: 0;
        padding: 0;
    }
    
");
?>

<div class="row equal">
	<?php foreach ($models as $model) : ?>
		<a class="thumbnail col-xs-12 col-sm-4 col-md-3 col-lg-2" href="<?= Url::to(['default/index', '_blog' => $model->name]) ?>"> 
			<?= Html::img(($model->logo ? AdminHelper::getImageUrl('logo', '400', $model->logo) : '@web/cdn/image/logo.png'), ['class' => 'img img-responsive',]); ?>
			<h3>
				<?= Html::encode($model->title) ?>
			</h3>
		</a>
	<?php endforeach ?>
</div>
<div class="row">
	<div class="col-sm-2 pb20" style="padding-right: 0px;padding-left: 0px;">
		<a type="button" class="btn btn-success btn-block" style="margin-top: 20px;" href="<?= Url::to(['site/create']) ?>"><?= Yii::t('app', 'Create {name}', ['name' => Yii::t('app', 'Blog')]) ?></a>
	</div>
</div>

