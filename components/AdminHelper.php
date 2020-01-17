<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class AdminHelper extends Component
{

    public static function url($config)
    {
        $blogName = Yii::$app->blog->name();
        return Url::to(['_blog' => $blogName] + $config);
    }

    public static function getImageUrl($type, $whq, $name)
    {
        return Url::toRoute(['/site/gallery', 'type' => $type, 'whq' => $whq, 'name' => $name], true);
    }

}
