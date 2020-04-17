<?php

namespace app\components;

use Exception;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use Yii;
use yii\base\Component;
use yii\imagine\Image as Imagine;

class Image extends Component
{

    public static $validTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    public static $validModes = [
        0 => 'NONE',
        1 => 'INBOUND',
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

    public function save($srcFile, $des, $width = null, $height = null, $quality = null, $desIsAbsolute = true, $mode = 0)
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
            $mode = (empty($mode) || !in_array($mode, array_keys(Image::$validModes)) ? 0 : intval($mode));

            $image = Imagine::getImagine()->open($srcFile);

            if ($width && $height) {
                
            } elseif ($width) {
                $height = ($width * $imageSize[1] ) / $imageSize[0];
            } elseif ($height) {
                $width = ($height * $imageSize[0] ) / $imageSize[1];
            } else {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }

            if ($mode == 1) {
                $image = Imagine::resize($image, $width, $height, true, true);
                $box = new Box($width, $height);
                //
                if ($imageSize[0] / $width < $imageSize[1] / $height) {
                    $scale = $height / $imageSize[1];
                } else {
                    $scale = $width / $imageSize[0];
                }
                $newWidth = intval($imageSize[0] * $scale);
                $newHeight = intval($imageSize[1] * $scale);
                //
                $color = (new RGB())->color('000', 0);
                $pasteTo = new Point(($width - $newWidth) / 2, ($height - $newHeight) / 2);
                //
                $image = Imagine::getImagine()->create($box, $color)->paste($image, $pasteTo);
            } else {
                $image = Imagine::resize($image, $width, $height, false, true);
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
        } catch (Exception $e) {
            
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

    public static function getCacheImagePath($type, $whqm, $name)
    {
        return self::getGalleryPath() . DIRECTORY_SEPARATOR . $type . '-' . $whqm . '-' . $name;
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
