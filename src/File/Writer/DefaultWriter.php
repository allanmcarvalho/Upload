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
use Cake\Filesystem\Folder;
use Cake\Utility\Hash;
use Intervention\Image\ImageManager;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
class DefaultWriter implements WriterInterface
{

    /**
     * Table Object
     * @var Table 
     */
    protected $table;

    /**
     * Entity Object
     * @var Entity 
     */
    protected $entity;

    /**
     * Name of field of file
     * @var string 
     */
    protected $field;

    /**
     * Array of settings
     * @var array 
     */
    protected $settings;

    /**
     * Info from file
     * @var array 
     */
    protected $fileInfo;

    /**
     * Default destination file path
     * @var string 
     */
    protected $defaultPath = '';

    /**
     * Final file name
     * @var string 
     */
    protected $fileName = null;

    /**
     * Construct Method
     * @param Table $table
     * @param Entity $entity
     * @param type $field
     * @param type $settings
     */
    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        $this->table       = $table;
        $this->entity      = $entity;
        $this->field       = $field;
        $this->settings    = $settings;
        $this->fileInfo    = $this->entity->get($this->field);
        $this->defaultPath = WWW_ROOT . 'files' . DS . $this->table->getAlias() . DS;
    }

    public function write()
    {
        
    }

    public function delete()
    {
        
    }

    /**
     * Get a path to save file
     * @return string
     */
    protected function getPath()
    {
        $path = Hash::get($this->settings, 'path', $this->defaultPath);

        return empty($path) ? $this->defaultPath : (substr($path, -1) === DS ? $path : $path . DS);
    }

    /**
     * Check if path exist
     * @param bool $create Create a path if not exist
     */
    protected function checkPath($create = true)
    {  
        if (!new Folder($this->getPath(), $create))
        {
            \Cake\Log\Log::error(__d('upload', 'Unable to create directory: {0}', $this->getPath()));
        }
    }

    /**
     * Return a file name
     * @return string
     */
    protected function getFileName()
    {
        if (debug_backtrace()[1]['function'] == 'write')
        {
            if ($this->fileName === null)
            {
                $filePrefix            = Hash::get($this->settings, 'prefix', '');
                $fileUniqidMoreEntropy = Hash::get($this->settings, 'more_entropy', true);
                $this->fileName        = Hash::get($this->settings, 'filename', uniqid($filePrefix, $fileUniqidMoreEntropy));
            }
        }elseif(debug_backtrace()[1]['function'] == 'delete')
        {
            $this->fileName = $this->entity->get($this->field);
        }
        return $this->fileName;
    }

}
