<?php

namespace app\components;

use yii\base\Component;

class BlogContainer extends Component
{

    private $_identity = null;

    public function setIdentity($identity)
    {
        $this->_identity = $identity;
    }

    public function getIdentity()
    {
        return $this->_identity;
    }

    public function hasIdentity()
    {
        return $this->_identity !== null;
    }

    public function attribute($attribute)
    {
        if ($this->_identity && $this->_identity->hasAttribute($attribute)) {
            return $this->_identity->$attribute;
        }
        return null;
    }

    public function name()
    {
        return $this->attribute('name');
    }

}
