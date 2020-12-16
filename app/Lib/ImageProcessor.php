<?php
/**
 * Created by PhpStorm.
 * User: yongj
 * Date: 7/26/18
 * Time: 11:09 AM
 */

namespace App\Lib;


class ImageProcessor
{
    public static function optimize($image_data, $need_force_resize = false) {

        ini_set('memory_limit','2048M');

        list($width, $height) = getimagesizefromstring($image_data);

        $w = $width;
        $h = $height;

        $image = imagecreatefromstring($image_data);

        if ($need_force_resize) {
            $width_limit = 1000;
            if ($width > $width_limit) {
                $w = $width_limit;
                $h = $height * ($width_limit / $width);
            }

            $resized_image = imagecreatetruecolor($w, $h);
            imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $w, $h, $width, $height);
        } else {
            $resized_image = $image;
        }

        ob_start();
        imagejpeg($resized_image, null, 50);
        //imagegif($resized_image);
        $new_image_data = ob_get_contents();
        ob_end_clean();

        return $new_image_data;
    }
}