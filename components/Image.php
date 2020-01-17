<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\imagine\Image as Imagine;

class Image extends Component
{
    public static $validTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    private $_info;
    private $_error;

    public function getInfo()
    {
        return $this->_info;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function save($srcFile, $des, $width = null, $height = null, $quality = null, $desIsAbsolute = true)
    {
        try {
            $this->setError(null);

            if (!file_exists($srcFile)) {
                return $this->setError(1);
            }

            $imageSize = getimagesize($srcFile);
            if (!$imageSize) {
                return $this->setError(2);
            }

            $mime = $imageSize['mime'];

            $ext = null;
            if (array_key_exists($mime, self::$validTypes)) {
                $ext = self::$validTypes[$mime];
            } else {
                return $this->setError(3);
            }

            /*
             *   SAVE PART
             */

            $width = (empty($width) || $width < 1 || $imageSize[0] * 3 < $width ? null : intval($width));
            $height = (empty($height) || $height < 1 || $imageSize[1] * 3 < $height ? null : intval($height));
            $quality = (empty($quality) || $quality < 1 || 100 < $quality ? 67 : intval($quality));


            $image = Imagine::getImagine()->open($srcFile);

            if ($width && $height) {
                $image = Imagine::resize($image, $width, $height, false, true);
            } elseif ($width) {
                $image = Imagine::resize($image, $width, null, true, true);
            } elseif ($height) {
                $image = Imagine::resize($image, null, $height, true, true);
            } else {
                $image = Imagine::resize($image, $imageSize[0], $imageSize[1], true, true);
            }

            if ($desIsAbsolute) {
                $pathinfo = pathinfo($des);
                $name = $pathinfo['basename'];
                $desFile = $des;
            } else {
                do {
                    $name = substr(uniqid(rand(), true), 0, 12) . '.' . $ext;
                    $desFile = self::getOriginalImagePath($des, $name);
                } while (file_exists($desFile));
            }

            $image->save($desFile, ['quality' => $quality]);

            if (file_exists($desFile)) {
                $desSize = $image->getSize();
                return $this->setError(null, [
                            'desWidth' => $desSize->getWidth(),
                            'desHeight' => $desSize->getHeight(),
                            'desName' => $name,
                            'desFile' => $desFile,
                ]);
            }
        } catch (\Exception $e) {
        }

        return $this->setError(-1);
    }

    private function setError($code, $info = null)
    {
        if ($code === null) {
            $this->_error = null;
        } elseif ($code == 1) {
            $this->_error = Yii::t('yii', 'Please upload a file.');
        } elseif ($code == 2) {
            $this->_error = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.', ['extensions' => implode(', ', self::$validTypes)]);
        } elseif ($code == 3) {
            $this->_error = Yii::t('yii', 'Only files with these extensions are allowed: {extensions}.', ['extensions' => implode(', ', self::$validTypes)]);
        } else {
            $this->_error = Yii::t('yii', 'Error');
        }

        return $this->_info = $info;
    }

    public static function getOriginalImagePath($type, $name)
    {
        return self::getGalleryPath() . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;
    }

    public static function getCacheImagePath($type, $whq, $name)
    {
        return self::getGalleryPath() . DIRECTORY_SEPARATOR . $type . '-' . $whq . '-' . $name;
    }

    public static function getGalleryPath()
    {
        $path = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'gallery';
        return self::buildPath($path);
    }

    public static function buildPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 755, true);
        }
        return $path;
    }
}
