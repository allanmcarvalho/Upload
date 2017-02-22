<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Filesystem\File;
use Cake\Utility\Hash;
use Intervention\Image\ImageManager;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
class ImageWriter extends DefaultWriter
{

    private $maxHeigth         = false;
    private $maxWidth          = false;
    private $watermark         = false;
    private $watermarkPosition = false;

    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        parent::__construct($table, $entity, $field, $settings);
        $this->defaultPath = WWW_ROOT . 'img' . DS . $this->table->getAlias() . DS;
    }

    public function write()
    {
        $this->checkPath();
        $image = $this->getImage($this->fileInfo['tmp_name']);

        $this->maxHeigth         = Hash::get($this->settings, 'image.max_height', false);
        $this->maxWidth          = Hash::get($this->settings, 'image.max_width', false);
        $this->watermark         = Hash::get($this->settings, 'image.watermark', false);
        $this->watermarkPosition = Hash::get($this->settings, 'image.watermark_position', 'bottom-right');

        $image = $this->modifyImage($image);

        if ($image->save("{$this->getPath()}{$this->getFileName()}{$this->getImageFormat()}", $this->getImageQuality()))
        {
            return $this->entity->set($this->field, "{$this->getFileName()}{$this->getImageFormat()}");
        } else
        {
            return false;
        }
    }

    public function delete()
    {
        $file = new File("{$this->getPath()}{$this->getFileName()}");
        if ($file->exists())
        {
            if (!$file->delete())
            {
                \Cake\Log\Log::error(__d('upload', 'Unable to delete file "{0}" in path "{1}"', $this->getFileName(), $this->getPath()));
            }
        } else
        {
            \Cake\Log\Log::error(__d('upload', 'Unable to delete file "{0}" in path "{1}" because it does not exist', $this->getFileName(), $this->getPath()));
        }
    }

    /**
     * Modifier function calls
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    private function modifyImage($image)
    {
        if ($this->maxHeigth !== false)
        {
            if ($this->maxHeigth < $image->height())
            {
                $image = $this->maxHeight($image, $this->maxHeigth);
            }
        }

        if ($this->maxWidth !== false)
        {
            if ($this->maxWidth < $image->width())
            {
                $image = $this->maxWidth($image, $this->maxWidth);
            }
        }

        if ($this->watermark !== false)
        {
            $image = $this->insertWatermark($image, $this->watermark, $this->watermarkPosition);
        }

        return $image;
    }

    /**
     * Get a Intervention image object
     * @param string $path
     * @return \Intervention\Image\Image
     */
    private function getImage($path)
    {
        $manager = new ImageManager();
        return $manager->make($path);
    }

    /**
     * Set a max width of image if it is smaller than original
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    private function maxWidth($image, $width)
    {
        $image->resize($width, null, function ($constraint)
        {
            $constraint->aspectRatio();
        });

        return $image;
    }

    /**
     * Set a max height of image if it is smaller than original
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    private function maxHeight($image, $heigt)
    {

        $image->resize(null, $heigt, function ($constraint)
        {
            $constraint->aspectRatio();
        });

        return $image;
    }

    /**
     * Insert a watermark in image
     * @param \Intervention\Image\Image $image
     * @param string $path
     * @param string $position
     * @return \Intervention\Image\Image
     */
    public function insertWatermark($image, $path, $position)
    {
        $watermark = $this->getImage($path);

        if($watermark->height() > intval($image->height() * 0.07))
        {
            $watermark = $this->maxHeight($watermark, intval($image->height() * 0.07));
        }

        $image->insert($watermark, $position, $image->width() * 0.05, $image->height() * 0.05);

        return $image;
    }

    private function getImageFormat()
    {
        $imageFormat = Hash::get($this->settings, 'image.format', 'jpg');
        return substr($imageFormat, 0, 1) === '.' ? $imageFormat : '.' . $imageFormat;
    }

    private function getImageQuality()
    {
        return Hash::get($this->settings, 'image.quality', 100);
    }

}
