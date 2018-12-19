<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\Hash;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
abstract class DefaultWriter implements WriterInterface
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
     * Destination file path
     * @var string 
     */
    protected $path = null;

    /**
     * Final file name
     * @var string 
     */
    protected $filename = null;

    /**
     * Construct Method
     * @param Table $table
     * @param Entity $entity
     * @param string $field
     * @param array $settings
     */
    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        $this->table       = $table;
        $this->entity      = $entity;
        $this->field       = $field;
        $this->settings    = $settings;
        $this->fileInfo    = (array) $this->entity->get($this->field);
        $this->defaultPath = WWW_ROOT . 'files' . DS . $this->table->getAlias() . DS;
    }

    /**
     * Delete a file from path
     * @param $path
     * @param $filename
     * @return boolean
     */
    protected function _delete($path, $filename)
    {
        $file = new File($path . $filename);
        if ($file->exists())
        {
            if (!$file->delete())
            {
                Log::error(__d('upload', 'Unable to delete file "{0}" in entity id "{1}" from table "{2}" and path "{3}"', $filename, $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $path));
                return false;
            }
        } else
        {
            Log::error(__d('upload', 'Unable to delete file "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $filename, $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $path));
            return false;
        }
        return true;
    }

    /**
     * Get a path to save file
     * @param null|string $subDirectory
     * @return string
     */
    protected function getPath($subDirectory = null)
    {
        if ($this->path === null)
        {
            $path       = Hash::get($this->settings, 'path', $this->defaultPath);
            $this->path = empty($path) ? $this->defaultPath : (substr($path, -1) === DS ? $path : $path . DS);

            if (!is_dir($this->path))
            {
                $this->createFolderIfItNotExists($this->path);
            }
        }

        if ($subDirectory !== null)
        {
            $subDirectory = substr($subDirectory, -1) === DS ? $subDirectory : $subDirectory . DS;
            $this->createFolderIfItNotExists($this->path . $subDirectory);
            return $this->path . $subDirectory;
        } else
        {
            return $this->path;
        }
    }

    /**
     * Create a folder if it not exist
     * @param string $path
     */
    protected function createFolderIfItNotExists($path)
    {
        if (!new Folder($path, true))
        {
            Log::error(__d('upload', 'Unable to create directory: {0}', $path));
        }
    }

    /**
     * get a image save format from behavior config
     * @return string
     */
    protected function getConfigFileFormat()
    {
        if (Hash::get($this->settings, 'image', false))
        {
            $fileExtension = Hash::get($this->settings, 'image.format', 'jpg');
            if (!in_array($fileExtension, ['jpg', 'png', 'gif', 'same']))
            {
                $fileExtension = 'jpg';
            }
            if ($fileExtension === 'same')
            {
                $fileExtension = pathinfo(Hash::get($this->fileInfo, 'name', 'err'), PATHINFO_EXTENSION);
                return substr($fileExtension, 0, 1) === '.' ? $fileExtension : '.' . $fileExtension;
            } else
            {
                return substr($fileExtension, 0, 1) === '.' ? $fileExtension : '.' . $fileExtension;
            }
        } else
        {
            $fileExtension = pathinfo(Hash::get($this->fileInfo, 'name', 'err'), PATHINFO_EXTENSION);
            return substr($fileExtension, 0, 1) === '.' ? $fileExtension : '.' . $fileExtension;
        }
    }

    /**
     * Create a new filename
     * @param bool $ifExistCreateNew if true force to create a new filename
     * @return string
     */
    protected function createFilename($ifExistCreateNew = false)
    {
        if ($this->filename === null)
        {
            $filePrefix            = Hash::get($this->settings, 'prefix', '');
            $fileUniqueMoreEntropy = Hash::get($this->settings, 'more_entropy', true);
            $this->filename        = Hash::get($this->settings, 'filename', uniqid($filePrefix, $fileUniqueMoreEntropy)) . $this->getConfigFileFormat();
        } elseif ($ifExistCreateNew === true)
        {
            $filePrefix            = Hash::get($this->settings, 'prefix', '');
            $fileUniqueMoreEntropy = Hash::get($this->settings, 'more_entropy', true);
            $this->filename        = Hash::get($this->settings, 'filename', uniqid($filePrefix, $fileUniqueMoreEntropy)) . $this->getConfigFileFormat();
        }
        return $this->filename;
    }

    /**
     * Return a file name
     * @return string
     */
    protected function getFilename()
    {
        if ($this->filename === null)
        {
            if ($this->entity->isNew())
            {
                $this->createFilename();
            } elseif (is_array($this->entity->get($this->field)))
            {
                $entity         = $this->table->get($this->entity->get($this->table->getPrimaryKey()));
                $this->filename = $entity->get($this->field);
            } else
            {
                $this->filename = $this->entity->get($this->field);
            }
        }

        return $this->filename;
    }

}
