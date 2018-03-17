<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Validation\Traits;

use Intervention\Image\ImageManager;

/**
 * Description of UploadTrait
 *
 * @author allan
 */
trait ImageTrait
{

    /**
     * 
     * @return ImageManager
     */
    protected static function getManager()
    {
        return new ImageManager();
    }

    /**
     * Get a Intervention image object
     * @param string $path
     * @return \Intervention\Image\Image
     */
    protected static function getImage($path)
    {
        return self::getManager()->make($path);
    }

    /**
     * Calculate greatest common divisor of $dividend_a and $dividend_b
     * @param int $dividend_a
     * @param int $dividend_b
     * @return int
     */
    protected static function greatestCommonDivisor($dividend_a, $dividend_b)
    {
        return ($dividend_a % $dividend_b) ? self::greatestCommonDivisor($dividend_b, $dividend_a % $dividend_b) : $dividend_b;
    }

    /**
     * Check that the file is above the minimum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isAboveMinWidth($check, $width)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        return $image->width() < $width ? false : true;
    }

    /**
     * Check that the file is below the maximum width requirement
     *
     * @param mixed $check Value to check
     * @param int $width Width of Image
     * @return bool Success
     */
    public static function isBelowMaxWidth($check, $width)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        return $image->width() > $width ? false : true;
    }

    /**
     * Check that the file is above the minimum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isAboveMinHeight($check, $height)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        return $image->height() < $height ? false : true;
    }

    /**
     * Check that the file is below the maximum height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isBelowMaxHeight($check, $height)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        return $image->height() > $height ? false : true;
    }


    /**
     * Check that the file has exact width requirement
     *
     * @param mixed $check Value to check
     * @param $width
     * @return bool Success
     */
    public static function isThisWidth($check, $width)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        return $image->width() == $width ? true : false;
    }

    /**
     * Check that the file has exact height requirement
     *
     * @param mixed $check Value to check
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isThisHeight($check, $height)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);
        return $image->height() == $height ? true : false;
    }

    /**
     * Check that the file has exact height requirement
     *
     * @param mixed $check Value to check
     * @param $width
     * @param int $height Height of Image
     * @return bool Success
     */
    public static function isThisWidthAndHeight($check, $width, $height)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);

        if($image->width() == $width and $image->height() == $height)
        {
            return true;
        }else
        {
            return false;
        }
    }

    /**
     * Checks if the image has a correct aspect ratio
     * @param array $check
     * @param int $width
     * @param int $height
     * @return boolean
     */
    public static function isThisAspectRatio($check, $width, $height)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $image = self::getImage($check['tmp_name']);
        $gcd   = self::greatestCommonDivisor($image->width(), $image->height());

        if (self::greatestCommonDivisor($width, $height) > 1)
        {
            $ratioGcd = self::greatestCommonDivisor($width, $height);
            $width    = $width / $ratioGcd;
            $height   = $height / $ratioGcd;
        }

        if (($image->width() / $gcd) !== $width or ( $image->height() / $gcd) !== $height)
        {
            return false;
        } else
        {
            return true;
        }
    }

    /**
     * Check the image extension
     * @param array $check
     * @param mixed $extensions
     * @return boolean
     */
    public static function isThisExtension($check, $extensions = [])
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        $fileExtension = pathinfo($check['name'], PATHINFO_EXTENSION);

        if (is_array($extensions))
        {
            foreach ($extensions as $key => $extension)
            {
                $extensions[$key] = str_replace('.', '', $extension);
            }

            if (in_array($fileExtension, $extensions))
            {
                return true;
            } else
            {
                return false;
            }
        } elseif (is_string($extensions))
        {
            return $fileExtension === $extensions ? true : false;
        }
        return false;
    }

}
