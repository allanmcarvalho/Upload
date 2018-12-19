<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\Filesystem\File;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\ORM\Table;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
class FileWriter extends DefaultWriter
{



    public function __construct(Table $table, Entity $entity, $field, $settings)
    {
        parent::__construct($table, $entity, $field, $settings);
        $this->defaultPath = WWW_ROOT . 'file' . DS . $this->table->getAlias() . DS;
    }

    /**
     * write a file
     * @return boolean|string
*/
    public function write()
    {
        if (!$this->entity->isNew())
        {
            $this->delete(true);
            $this->createFilename(true);
        }
        
        $file = new File($this->fileInfo['tmp_name'], true);

        if ($file->copy("{$this->getPath()}{$this->getFilename()}"))
        {
            return $this->entity->set($this->field, "{$this->getFileName()}");
        } else
        {
            Log::error(__d('upload', 'Unable to salve file "{0}" in entity id "{1}" from table "{2}" and path "{3}" because it does not exist', $this->getFileName(), $this->entity->get($this->table->getPrimaryKey()), $this->table->getTable(), $this->getPath()));
            return false;
        }
    }

    /**
     * Delete method that delete primary and thumbnails images
     * @param bool $isUpdate
     * @return bool
     */
    public function delete($isUpdate = false)
    {
        if($isUpdate === false)
        {
            $entity = &$this->entity;
        }else
        {
            $entity = $this->table->get($this->entity->get($this->table->getPrimaryKey()));
        }
        
        if (!empty($entity->get($this->field)))
        {
            $filename = $entity->get($this->field);
            return $this->_delete($this->getPath(), $filename);
        }
    }

}
