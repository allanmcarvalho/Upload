<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Image;
use Cake\Utility\Hash;

/**
 * Description of ImageManager
 *
 * @author allan
 */
trait ImageTrait
{

    /**
     * 
     * @return ImageManager
     */
    protected function getManager()
    {
        return new ImageManager();
    }

    /**
     * Get a Intervention image object
     * @param string $path
     * @return \Intervention\Image\Image
     */
    protected function getImage($path)
    {
        return $this->getManager()->make($path);
    }

    /**
     * Resize a imagem based in 
     * @param Image $image
     * @param mixed $width must by smaller than the original
     * @param mixed $height must by smaller than the original
     * @return Image
     */
    protected function resize($image, $width, $height, $minSize)
    {
        if ($width === false and $height === false and $minSize !== false)
        {
            if ($image->width() < $image->height())
            {
                $width = $minSize;
            } elseif ($image->height() < $image->width())
            {
                $height = $minSize;
            } elseif ($image->height() == $image->width())
            {
                $width = $minSize;
            }
        }
        if ($width !== false or $height !== false)
        {
            if ($width === false)
            {
                $width = $this->getEquivalentResizeWidth($image, $height);
            } else
            {
                $width = $width <= $image->width() ? $width : $image->width();
            }

            if ($height === false)
            {
                $height = $this->getEquivalentResizeHeight($image, $width);
            } else
            {
                $height = $height <= $image->height() ? $height : $image->height();
            }
            $image->resize($width, $height);
        }
    }

    /**
     * Crop a image
     * @param Image $image
     * @param mixed $width
     * @param mixed $height
     * @return Image
     */
    protected function crop($image, $width, $height, $x, $y)
    {
        if ($width !== false or $height !== false)
        {
            if ($width === false)
            {
                $width = $height <= $image->width() ? $height : $image->width();
            } else
            {
                $width = $width <= $image->width() ? $width : $image->width();
            }

            if ($height === false)
            {
                $height = $width <= $image->height() ? $width : $image->height();
            } else
            {
                $height = $height <= $image->height() ? $height : $image->height();
            }

            if ($x != null and ( $x + $width) > $image->width())
            {
                $x = $image->width() - $width;
            }
            if ($y != null and ( $y + $height) > $image->height())
            {
                $y = $image->height() - $height;
            }
            $image->crop($width, $height, $x, $y);
        }
    }

    /**
     * Insert a watermark in image
     * @param \Intervention\Image\Image $image
     * @param string $path
     * @param string $position
     * @return \Intervention\Image\Image
     */
    protected function insertWatermark($image, $path, $position, $opacity)
    {
        $watermark               = $this->getImage($path);
        $targetHeight            = intval($image->height() * 0.07);
        $targetWarthermarkHeight = $targetHeight <= $watermark->height() ? $targetHeight : $watermark->height();
        $targetWarthermarkWidth  = $this->getEquivalentResizeWidth($watermark, $targetWarthermarkHeight);

        if ($targetWarthermarkWidth > $image->width())
        {
            $targetWidth             = intval($image->width() * 0.8);
            $targetWarthermarkWidth  = $targetWidth <= $watermark->width() ? $targetWidth : $watermark->width();
            $targetWarthermarkHeight = $this->getEquivalentResizeHeight($image, $targetWarthermarkWidth);
        }

        $this->resize($watermark, $targetWarthermarkWidth, $targetWarthermarkHeight, false);

        $watermark->opacity($opacity);

        $image->insert($watermark, $position, round($image->width() * 0.05), round($image->height() * 0.05));
    }

    /**
     * 
     * @param \Intervention\Image\Image $thumbnailImage
     * @return boolean
     */
    protected function createThumbnails()
    {
        if ($this->thumbnails === false)
        {
            return false;
        }
        foreach ($this->thumbnails as $thumbnail)
        {
            $newThumbnail = $this->getImage($this->fileInfo['tmp_name']);
            $width        = Hash::get($thumbnail, 'resize.width', false);
            $height       = Hash::get($thumbnail, 'resize.height', false);
            $minSize      = Hash::get($thumbnail, 'resize.min_size', false);
            $cropWidth    = Hash::get($thumbnail, 'crop.width', false);
            $cropHeight   = Hash::get($thumbnail, 'crop.height', false);
            $cropX        = Hash::get($thumbnail, 'crop.x', null);
            $cropY        = Hash::get($thumbnail, 'crop.y', null);
            $label        = Hash::get($thumbnail, 'label', false);

            if($width === false and $height === false and $minSize === false and $cropWidth === false and $cropHeight === false)
            {
                continue;
            }
            
            if($label === false)
            {
                continue;
            }
            
            if ($width === false and $height === false and $minSize !== false)
            {
                if ($newThumbnail->width() < $newThumbnail->height())
                {
                    $width = $minSize;
                } elseif ($newThumbnail->height() < $newThumbnail->width())
                {
                    $height = $minSize;
                } elseif ($newThumbnail->height() == $newThumbnail->width())
                {
                    $width = $minSize;
                }
            }

            if ($width === false)
            {
                $width = $this->getEquivalentResizeWidth($newThumbnail, $height);
            }

            if ($height === false)
            {
                $height = $this->getEquivalentResizeHeight($newThumbnail, $width);
            }

            $this->resize($newThumbnail, $width, $height, $minSize);

            if ($cropWidth !== false or $cropHeight !== false)
            {
                $this->crop($newThumbnail, $cropWidth, $cropHeight, $cropX, $cropY);
            }

            $watermarkPath = Hash::get($thumbnail, 'watermark.path', $this->watermark);
            if (empty($watermarkPath))
            {
                $watermarkPath = $this->watermark;
            }
            if ($watermarkPath !== false and Hash::get($thumbnail, 'watermark', true))
            {
                $watermarkPosition = Hash::get($thumbnail, 'watermark.position', $this->watermark_position);
                $watermarkOpacity  = Hash::get($thumbnail, 'watermark.opacity', $this->watermark_opacity);
                $this->insertWatermark($newThumbnail, $watermarkPath, $watermarkPosition, $watermarkOpacity);
            }
            $newThumbnail->interlace(true);
            if (!$newThumbnail->save($this->getPath($label) . $this->getFilename(), $this->getConfigImageQuality()))
            {
                \Cake\Log\Log::error(__d('upload', 'Unable to salve thumbnail "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
            }
            unset($newThumbnail);
        }
    }

    /**
     * Calculate greatest common divisor of $dividend_a and $dividend_b
     * @param int $dividend_a
     * @param int $dividend_b
     * @return int
     */
    private function greatestCommonDivisor($dividend_a, $dividend_b)
    {
        return ($dividend_a % $dividend_b) ? $this->greatestCommonDivisor($dividend_b, $dividend_a % $dividend_b) : $dividend_b;
    }

    /**
     * 
     * @param Image $image
     * @param int $newImageHeight
     * @return int
     */
    private function getEquivalentResizeWidth($image, $newImageHeight)
    {
        $imageWidht  = $image->width();
        $imageHeight = $image->height();
        $gcd         = round($this->greatestCommonDivisor($imageWidht, $imageHeight));
        $widthRadio  = round($imageWidht / $gcd);
        $heigthRadio = round($imageHeight / $gcd);
        return round(($newImageHeight / $heigthRadio) * $widthRadio);
    }

    private function getEquivalentResizeHeight($image, $newImageWidth)
    {
        $imageWidht  = $image->width();
        $imageHeight = $image->height();
        $gcd         = round($this->greatestCommonDivisor($imageWidht, $imageHeight));
        $widthRadio  = round($imageWidht / $gcd);
        $heigthRadio = round($imageHeight / $gcd);
        return round(($newImageWidth / $widthRadio) * $heigthRadio);
    }

}
