<?php

namespace RedefineLab\ImageService;

class ImageService {

    const ORIENTATION_PORTRAIT = 'portrait';
    const ORIENTATION_LANDSCAPE = 'landscape';
    const ORIENTATION_SQUARE = 'square';


    private $image;
    private $image_type;

    public function load($filename) {

        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
    }

    public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }

        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }

    public function output($image_type=IMAGETYPE_JPEG) {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }

    public function getWidth() {

        return imagesx($this->image);
    }

    public function getHeight() {

        return imagesy($this->image);
    }

    public function resizeToHeight($height) {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    public function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    public function scale($scale) {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    public function resize($width, $height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

    public function crop($finalWidth, $finalHeight, $allowChangeOrientation = TRUE) {
        $finalOrientation = $this->getOrientation($finalWidth, $finalHeight);
        $imageOrientation = $this->getOrientation($this->getWidth(), $this->getHeight());

        if ($allowChangeOrientation && $finalOrientation != self::ORIENTATION_SQUARE && $finalOrientation != $imageOrientation) {
            $this->swap($finalWidth, $finalHeight);
            $finalOrientation = $this->getOrientation($finalWidth, $finalHeight);
        }

        $ownRatio = $this->getWidth() / $this->getHeight();
        $finalRatio = $finalWidth / $finalHeight;

        $src_x = $src_y = 0;
        $width = $this->getWidth();
        $height = $this->getHeight();
        if ($ownRatio < $finalRatio) {
            $height = $this->getHeight() / $finalRatio * $ownRatio;
            $src_y = ($this->getHeight() - $height) / 2;
        } else if ($ownRatio > $finalRatio) {
            $width = $this->getWidth() / $ownRatio * $finalRatio;
            $src_x = ($this->getWidth() - $width) / 2;
        }

        $new_image = imagecreatetruecolor($finalWidth, $finalHeight);
        imagecopyresampled($new_image, $this->image, 0, 0, $src_x, $src_y, $finalWidth, $finalHeight, $width, $height);
        $this->image = $new_image;
    }

    public function getOrientation($width = '', $height = '') {
        // If width or height is not provided, we assume we want the know the
        // orientation of the current image.
        if ($width == '' || $height == '') {
            $width = $this->getWidth();
            $height = $this->getHeight();
        }

        if ($width == $height) {
            return self::ORIENTATION_SQUARE;
        }

        if ($width > $height) {
            return self::ORIENTATION_LANDSCAPE;
        }

        return self::ORIENTATION_PORTRAIT;
    }

    private function swap(&$a, &$b) {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }

}