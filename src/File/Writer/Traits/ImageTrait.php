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
    protected function resize($image, $width, $height)
    {
        if ($width != false and $height != false)
        {
            if ($width < $image->width() and $height < $image->height())
            {
                $image->resize($width, $height);
            }
        } elseif ($width != false)
        {
            if ($width < $image->width())
            {
                $image->resize($width, null, function ($constraint)
                {
                    $constraint->aspectRatio();
                });
            }
        } elseif ($height != false)
        {
            if ($height < $image->height())
            {
                $image->resize(null, $height, function ($constraint)
                {
                    $constraint->aspectRatio();
                });
            }
        }
        return $image;
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
        $image->crop($width, $height, $x, $y);
    }

    /**
     * Insert a watermark in image
     * @param \Intervention\Image\Image $image
     * @param string $path
     * @param string $position
     * @return \Intervention\Image\Image
     */
    protected function insertWatermark($image, $path, $position)
    {
        $watermark             = $this->getImage($path);
        $targetWarthermarkSize = intval($image->height() * 0.07);
        if ($watermark->height() > $targetWarthermarkSize)
        {
            $watermark = $this->resize($watermark, null, $targetWarthermarkSize);
        }

        $image->insert($watermark, $position, intval($image->width() * 0.05), intval($image->height() * 0.05));
    }

    /**
     * 
     * @param \Intervention\Image\Image $thumbnailImage
     * @return boolean
     */
    protected function createThumbnails($thumbnailImage)
    {
        foreach ($this->thumbnails as $thumbnail)
        {
            $width      = Hash::get($thumbnail, 'width');
            $height     = Hash::get($thumbnail, 'height');
            $cropWidth  = Hash::get($thumbnail, 'crop.width', false);
            $cropHeight = Hash::get($thumbnail, 'crop.height', false);
            $cropX      = Hash::get($thumbnail, 'crop.x', null);
            $cropY      = Hash::get($thumbnail, 'crop.y', null);

            $this->resize($thumbnailImage, $width, $height);

            if ($cropWidth !== false and $cropHeight !== false)
            {
                $this->crop($thumbnailImage, $cropWidth, $cropHeight, $cropX, $cropY);
                $thumbnailPath = $this->getPath("{$cropWidth}x{$cropHeight}");
            } else
            {
                $thumbnailPath = $this->getPath("{$width}x{$height}");
            }

            if (Hash::get($thumbnail, 'watermark', true))
            {
                if ($this->watermark !== false)
                {
                    $this->insertWatermark($thumbnailImage, $this->watermark, $this->watermarkPosition);
                }
            }
            if (!$thumbnailImage->save($thumbnailPath . $this->getFilename(), $this->getConfigImageQuality()))
            {
                \Cake\Log\Log::error(__d('upload', 'Unable to salve thumbnail "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
            }
        }
    }

}
