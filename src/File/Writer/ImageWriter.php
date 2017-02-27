<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\ORM\Table;
use Cake\ORM\Entity;
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

    /**
     * value of resize height
     * @var int 
     */
    private $resize_heigth = false;

    /**
     * value of resize width
     * @var int 
     */
    private $resize_width = false;

    /**
     * value of crop height
     * @var int 
     */
    private $crop_heigth = false;

    /**
     * value of crop width
     * @var int 
     */
    private $crop_width = false;

    /**
     * value of crop x position
     * @var int 
     */
    private $crop_x = false;

    /**
     * value of crop y position
     * @var int 
     */
    private $crop_y = false;

    /**
     * value of watermark file path
     * @var string 
     */
    private $watermark = false;

    /**
     * position of watermark
     * @var string 
     */
    private $watermark_position = false;

    /**
     * opacity of watermark
     * @var string 
     */
    private $watermark_opacity = false;

    /**
     * ignore watermark on default image
     * @var bool 
     */
    private $watermark_ignore_default = false;

    /**
     * array with thumbnails settings
     * @var array 
     */
    private $thumbnails = [];

    /**
     * Constructor method
     * @param Table $table
     * @param Entity $entity
     * @param type $field
     * @param type $settings
     */
    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        parent::__construct($table, $entity, $field, $settings);
        $this->defaultPath = WWW_ROOT . 'img' . DS . $this->table->getAlias() . DS;

        $this->resize_heigth            = Hash::get($this->settings, 'image.resize.height', false);
        $this->resize_width             = Hash::get($this->settings, 'image.resize.width', false);
        $this->crop_heigth              = Hash::get($this->settings, 'image.crop.height', false);
        $this->crop_width               = Hash::get($this->settings, 'image.crop.width', false);
        $this->crop_x                   = Hash::get($this->settings, 'image.crop.x', null);
        $this->crop_y                   = Hash::get($this->settings, 'image.crop.y', null);
        $this->watermark                = Hash::get($this->settings, 'image.watermark.path', false);
        $this->watermark_position       = Hash::get($this->settings, 'image.watermark.position', 'bottom-right');
        $this->watermark_opacity        = Hash::get($this->settings, 'image.watermark.opacity', 100);
        $this->watermark_ignore_default = Hash::get($this->settings, 'image.watermark.ignore_default', false);
        $this->thumbnails               = Hash::get($this->settings, 'image.thumbnails', []);
    }

    /**
     * write a image
     * @return boolean
     */
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
     * delete images
     * @param bool $isUptade
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

        $result = true;
        if (!empty($entity->get($this->field)))
        {
            $filename = $entity->get($this->field);
            if (!$this->deleteThubnails($this->getPath(), $filename))
            {
                $result = false;
            }
            if (!$this->_delete($this->getPath(), $filename))
            {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Delete image thumbnails
     * @param \Intervention\Image\Image $image
     */
    public function deleteThubnails($path, $filename)
    {
        if (!is_file($path . $filename))
        {
            return false;
        }
        $image  = $this->getImage($path . $filename);
        $result = true;
        foreach ($this->thumbnails as $thumbnail)
        {
            $width      = Hash::get($thumbnail, 'width', false);
            $height     = Hash::get($thumbnail, 'height', false);
            $cropWidth  = Hash::get($thumbnail, 'crop.width', false);
            $cropHeight = Hash::get($thumbnail, 'crop.height', false);
            $label      = Hash::get($thumbnail, 'label', false);

            if ($width === false and $height === false)
            {
                return false;
            }

            if ($width === false)
            {
                $newWidth = $this->getEquivalentResizeWidth($image, $height);
                $width    = $newWidth <= $image->width() ? $newWidth : $image->width();
            } else
            {
                $width = $width <= $image->width() ? $width : $image->width();
            }

            if ($height === false)
            {
                $newHeight = $this->getEquivalentResizeHeight($image, $width);
                $height    = $newHeight <= $image->height() ? $newHeight : $image->height();
            } else
            {
                $height = $height <= $image->height() ? $height : $image->height();
            }

            if ($label == !false)
            {
                if (!$this->_delete($this->getPath($label), $filename))
                {
                    $result = false;
                }
            } else
            {
                if ($cropWidth !== false or $cropHeight !== false)
                {
                    if ($cropWidth === false)
                    {
                        $cropWidth = $cropHeight <= $width ? $cropHeight : $width;
                    } else
                    {
                        $cropWidth = $cropWidth <= $width ? $cropWidth : $width;
                    }

                    if ($cropHeight === false)
                    {
                        $cropHeight = $cropWidth <= $height ? $cropWidth : $height;
                    } else
                    {
                        $cropHeight = $cropHeight <= $height ? $cropHeight : $height;
                    }

                    if (!$this->_delete($this->getPath("{$cropWidth}x{$cropHeight}"), $filename))
                    {
                        $result = false;
                    }
                } else
                {
                    if (!$this->_delete($this->getPath("{$width}x{$height}"), $filename))
                    {
                        $result = false;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Modifier function calls
     * @param \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    private function modifyImage($image)
    {
        $this->resize($image, $this->resize_width, $this->resize_heigth);

        $this->crop($image, $this->crop_width, $this->crop_heigth, $this->crop_x, $this->crop_y);

        $this->createThumbnails();

        if ($this->watermark !== false and $this->watermark_ignore_default !== true)
        {
            $this->insertWatermark($image, $this->watermark, $this->watermark_position, $this->watermark_opacity);
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
