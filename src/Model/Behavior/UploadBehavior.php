<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Model\Behavior;

use Cake\ORM\Behavior;
use Intervention\Image\Image;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Cake\Database\Type;
use Cake\ORM\Entity;

/**
 * CakePHP UploadBehavior
 * @author allancarvalho
 */
class UploadBehavior extends Behavior
{

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_config = [];
        $configs       = [];
        foreach ($config as $field => $settings)
        {
            if (is_int($field))
            {
                $configs[$settings] = [];
            } else
            {
                $configs[$field] = $settings;
            }
        }
        $this->setConfig($configs);

        Type::map('upload.file', 'Upload\Database\Type\FileType');
        $schema = $this->_table->getSchema();
        foreach (array_keys($this->getConfig()) as $field)
        {
            $schema->columnType($field, 'upload.file');
        }
        $this->_table->schema($schema);
    }

    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $validator = $this->_table->validator();
        $dataArray = $data->getArrayCopy();
        foreach (array_keys($this->getConfig()) as $field)
        {
            if (!$validator->isEmptyAllowed($field, false))
            {
                continue;
            }
            if (Hash::get($dataArray, $field . '.error') !== UPLOAD_ERR_NO_FILE)
            {
                continue;
            }
            unset($data[$field]);
        }
    }

    /**
     * 
     * @param Event $event
     * @param EntityInterface $entity
     * @param \ArrayObject $options
     * @return boolean
     */
    public function beforeSave(Event $event, EntityInterface $entity, \ArrayObject $options)
    {
        foreach ($this->getConfig() as $field => $settings)
        {
            if ($entity->has($field) and $entity->dirty($field))
            {
                if (Hash::get((array) $entity->get($field), 'error') != UPLOAD_ERR_OK)
                {
                    \Cake\Log\Log::write(\Psr\Log\LogLevel::ERROR, __d('upload', 'File upload had the following error: {0}', $this->getUploadError($entity->get($field)['error'])));
                    return false;
                }

                $writer = $this->getWriter($entity, $field, $settings);
                if (!$writer->write())
                {
                    return false;
                }
            }
        }
    }

    public function afterDelete(Event $event, Entity $entity, \ArrayObject $options)
    {
        foreach ($this->getConfig() as $field => $settings)
        {
            if ($entity->has($field))
            {
                $writer = $this->getWriter($entity, $field, $settings);
                if (!$writer->delete())
                {
                    $result = false;
                }
            }
        }
        if (isset($result))
        {
            return $result;
        }
    }

    /**
     * Delete file(s) without delete entity
     * @param EntityInterface $entity
     * @param array $fields
     * @return boolean
     */
    public function deleteFiles($entity, $fields = [])
    {
        if (empty($fields))
        {
            $configs = $this->getConfig();
        } else
        {
            foreach ($fields as $field => $settings)
            {
                if (is_int($field))
                {
                    $configs[$settings] = [];
                } else
                {
                    $configs[$field] = $settings;
                }
            }
            $configs = array_intersect_key($this->getConfig(), $configs);
        }

        if (empty($configs))
        {
            return false;
        }

        $result = true;
        foreach ($configs as $field => $settings)
        {
            if (!empty($entity->get($field)))
            {
                $writer = $this->getWriter($entity, $field, $settings);
                if (!$writer->delete())
                {
                    $result = false;
                }
                $entity->set($field, null);
            }
        }

        if (!$this->_table->save($entity))
        {
            return false;
        }

        return $result;
    }

    /**
     * 
     * @param Entity $entity
     * @param string $field
     * @param array $settings
     * @return \Upload\File\Writer\DefaultWriter
     * @throws UnexpectedValueException
     */
    private function getWriter(Entity $entity, $field, $settings)
    {
        $default     = isset($settings['image']) ? 'Upload\File\Writer\ImageWriter' : 'Upload\File\Writer\FileWriter';
        $writerClass = Hash::get($settings, 'writer', $default);
        if (is_subclass_of($writerClass, 'Upload\File\Writer\WriterInterface'))
        {
            return new $writerClass($this->_table, $entity, $field, $settings);
        }
        throw new UnexpectedValueException(__d('upload', "'writer' not set to instance of WriterInterface: {0}", $writerClass));
    }

    /**
     * Returns the type of upload error generated by PHP
     * @param int $error
     * @return string
     */
    private function getUploadError($error)
    {
        switch ($error)
        {
            case UPLOAD_ERR_OK:
                $result = __d('upload', 'There is no error, the file uploaded with success');
                break;
            case UPLOAD_ERR_INI_SIZE:
                $result = __d('upload', 'The uploaded file exceeds the upload_max_filesize directive in php.ini');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $result = __d('upload', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
                break;
            case UPLOAD_ERR_PARTIAL:
                $result = __d('upload', 'The uploaded file was only partially uploaded');
                break;
            case UPLOAD_ERR_NO_FILE:
                $result = __d('upload', 'No file was uploaded');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $result = __d('upload', 'Missing a temporary folder');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $result = __d('upload', 'Failed to write file to disk');
                break;
            case UPLOAD_ERR_EXTENSION:
                $result = __d('upload', 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help');
                break;
            default :
                $result = __d('upload', 'Unknown error {0}', $error);
                break;
        }
        return $result;
    }

}
