<?php

namespace App;

class ImageResize
{
    public static $image;
    public static $image_type;
    public static $filename;

    static public function load($filename) {
        self::$filename = $filename;
        $image_info = getimagesize($filename);
        self::$image_type = $image_info[2];

        if( self::$image_type == IMAGETYPE_JPEG ) {
            self::$image = imagecreatefromjpeg($filename);
        } elseif( self::$image_type == IMAGETYPE_GIF ) {
            self::$image = imagecreatefromgif($filename);
        } elseif( self::$image_type == IMAGETYPE_PNG ) {
            self::$image = imagecreatefrompng($filename); }
    }

    static public function save($image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
        $filename = self::$filename;
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg(self::$image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif(self::$image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng(self::$image,$filename);
        }

        if( $permissions != null) {
            chmod($filename,$permissions);
        }
    }

    static public function output($image_type=IMAGETYPE_JPEG) {
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg(self::$image);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif(self::$image);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng(self::$image);
        }
    }

    static public function getWidth() {
        return imagesx(self::$image);
    }

    static public function getHeight() {
        return imagesy(self::$image);
    }

    static public function resizeToHeight($height) {
        $ratio = $height / self::getHeight();
        $width = self::$getWidth() * $ratio;
        self::resize($width,$height);
    }

    static public function resizeToWidth($width) {
        $ratio = $width / self::getWidth();
        $height = self::getheight() * $ratio;
        self::resize($width,$height);
    }

    static public function scale($scale) {
        $width = self::getWidth() * $scale/100;
        $height = self::getheight() * $scale/100;
        self::resize($width,$height);
    }

    static public function resize($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, $width, $height, self::getWidth(), self::getHeight());
        self::$image = $new_image;
    }
}
