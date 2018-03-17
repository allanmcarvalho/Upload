<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use Upload\File\Writer\Traits\ImageTrait;
use Cake\Filesystem\File;


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
    private $resize_height = false;

    /**
     * value of resize width
     * @var int 
     */
    private $resize_width = false;

    /**
     * Make resize with min image size
     * @var int 
     */
    private $resize_min_size = false;

    /**
     * value of crop height
     * @var int 
     */
    private $crop_height = false;

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
     * Preserves the animation when extension is gif
     * @var bool 
     */
    private $preserveGifAnimation = false;

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
     * @param string $field
     * @param array $settings
     */
    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        parent::__construct($table, $entity, $field, $settings);
        $this->defaultPath = WWW_ROOT . 'img' . DS . $this->table->getAlias() . DS;

        $this->resize_height            = Hash::get($this->settings, 'image.resize.height', false);
        $this->resize_width             = Hash::get($this->settings, 'image.resize.width', false);
        $this->resize_min_size          = Hash::get($this->settings, 'image.resize.min_size', false);
        $this->crop_height              = Hash::get($this->settings, 'image.crop.height', false);
        $this->crop_width               = Hash::get($this->settings, 'image.crop.width', false);
        $this->crop_x                   = Hash::get($this->settings, 'image.crop.x', null);
        $this->crop_y                   = Hash::get($this->settings, 'image.crop.y', null);
        $this->preserveGifAnimation     = Hash::get($this->settings, 'image.preserve_animation', false);
        $this->watermark                = Hash::get($this->settings, 'image.watermark.path', false);
        $this->watermark_position       = Hash::get($this->settings, 'image.watermark.position', 'bottom-right');
        $this->watermark_opacity        = Hash::get($this->settings, 'image.watermark.opacity', 100);
        $this->watermark_ignore_default = Hash::get($this->settings, 'image.watermark.ignore_default', false);
        $this->thumbnails               = Hash::get($this->settings, 'image.thumbnails', []);
    }

    /**
     * write a image
     * @return boolean|string
     */
    public function write()
    {
        if (!$this->entity->isNew())
        {
            $this->delete(true);
            $this->createFilename(true);
        }

        if ($this->preserveGifAnimation === true and $this->getConfigFileFormat() == '.gif')
        {
            $file = new File($this->fileInfo['tmp_name'], true);

            if ($file->copy("{$this->getPath()}{$this->getFilename()}"))
            {
                return $this->entity->set($this->field, "{$this->getFileName()}");
            } else
            {
                Log::error(__d('upload', 'Unable to salve image "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
                return false;
            }
        } else
        {
            $image = $this->getImage($this->fileInfo['tmp_name']);

            $this->modifyImage($image);
            
            $image->interlace(true);
            if ($image->save("{$this->getPath()}{$this->getFilename()}", $this->getConfigImageQuality()))
            {
                return $this->entity->set($this->field, "{$this->getFileName()}");
            } else
            {
                Log::error(__d('upload', 'Unable to salve image "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
                return false;
            }
        }
    }

    /**
     * delete images
     * @param bool $isUpdate
     * @return bool
     */
    public function delete($isUpdate = false)
    {
        if ($isUpdate === false)
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
     * @param $path
     * @param $filename
     * @return bool
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
            $label = Hash::get($thumbnail, 'label', false);

            if ($label === false)
            {
                continue;
            }

            if (!$this->_delete($this->getPath($label), $filename))
            {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Modifier function calls
     * @param \Intervention\Image\Image $image
     * @return void
     */
    private function modifyImage($image)
    {
        $this->resize($image, $this->resize_width, $this->resize_height, $this->resize_min_size);

        $this->crop($image, $this->crop_width, $this->crop_height, $this->crop_x, $this->crop_y);

        $this->createThumbnails();

        if ($this->watermark !== false and $this->watermark_ignore_default !== true)
        {
            $this->insertWatermark($image, $this->watermark, $this->watermark_position, $this->watermark_opacity);
        }
    }

    /**
     * get a image quality from behavior config
     * @return int
     */
    private function getConfigImageQuality()
    {
        return Hash::get($this->settings, 'image.quality', 100);
    }

}
