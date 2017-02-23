<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Utility\Hash;
use Upload\File\Writer\Traits\ImageTrait;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
class ImageWriter extends DefaultWriter
{

    use ImageTrait;

    private $resize_heigth     = false;
    private $resize_width      = false;
    private $crop_heigth       = false;
    private $crop_width        = false;
    private $crop_x            = false;
    private $crop_y            = false;
    private $watermark         = false;
    private $watermarkPosition = false;
    private $thumbnails        = [];

    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        parent::__construct($table, $entity, $field, $settings);
        $this->defaultPath = WWW_ROOT . 'img' . DS . $this->table->getAlias() . DS;

        $this->resize_heigth     = Hash::get($this->settings, 'image.resize.height', false);
        $this->resize_width      = Hash::get($this->settings, 'image.resize.width', false);
        $this->crop_heigth       = Hash::get($this->settings, 'image.crop.height', false);
        $this->crop_width        = Hash::get($this->settings, 'image.crop.width', false);
        $this->crop_x            = Hash::get($this->settings, 'image.crop.x', null);
        $this->crop_y            = Hash::get($this->settings, 'image.crop.y', null);
        $this->watermark         = Hash::get($this->settings, 'image.watermark', false);
        $this->watermarkPosition = Hash::get($this->settings, 'image.watermark_position', 'bottom-right');
        $this->thumbnails        = Hash::get($this->settings, 'image.thumbnails', []);
    }

    public function write()
    {
        if (!$this->entity->isNew())
        {
            $this->delete(true);
            $this->createFilename(true);
        }



        $image = $this->getImage($this->fileInfo['tmp_name']);

        $this->modifyImage($image);

        if ($image->save("{$this->getPath()}{$this->getFilename()}", $this->getConfigImageQuality()))
        {
            return $this->entity->set($this->field, "{$this->getFileName()}");
        } else
        {
            \Cake\Log\Log::error(__d('upload', 'Unable to salve image "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
            return false;
        }
    }

    /**
     * Delete method that delete primary and thumbnails images
     */
    public function delete($isUptade = false)
    {
        if ($isUptade === false)
        {
            $entity = &$this->entity;
        } else
        {
            $entity = $this->table->get($this->entity->get($this->table->getPrimaryKey()));
        }

        if (!empty($entity->get($this->field)))
        {
            $filename = $entity->get($this->field);
            $this->_delete($this->getPath(), $filename);
            $result   = false;
            foreach ($this->thumbnails as $thumbnail)
            {
                $width      = Hash::get($thumbnail, 'width');
                $height     = Hash::get($thumbnail, 'height');
                $cropWidth  = Hash::get($thumbnail, 'crop.width', false);
                $cropHeight = Hash::get($thumbnail, 'crop.height', false);

                if ($cropWidth !== false and $cropHeight !== false)
                {
                    $result = $this->_delete($this->getPath("{$cropWidth}x{$cropHeight}"), $filename);
                } else
                {
                    $result = $this->_delete($this->getPath("{$width}x{$height}"), $filename);
                }
            }
            return $result;
        }
    }

    /**
     * Modifier function calls
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    private function modifyImage($image)
    {
        if ($this->resize_width !== false or $this->resize_heigth !== false)
        {
            $this->resize($image, $this->resize_width, $this->resize_heigth);
        }

        if ($this->crop_width !== false and $this->crop_heigth !== false)
        {
            $this->crop($image, $this->crop_width, $this->crop_heigth, $this->crop_x, $this->crop_y);
        }

        if ($this->thumbnails !== false)
        {
            $this->createThumbnails(clone $image);
        }
        
        if ($this->watermark !== false)
        {
            $this->insertWatermark($image, $this->watermark, $this->watermarkPosition);
        }
    }

    /**
     * get a image quality from behavior config
     * @return type
     */
    private function getConfigImageQuality()
    {
        return Hash::get($this->settings, 'image.quality', 100);
    }

}
