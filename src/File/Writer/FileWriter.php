<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\Filesystem\File;
use Cake\Utility\Hash;
use Cake\Log\Log;

/**
 * Description of DefaultWriter
 *
 * @author allancarvalho
 */
class FileWriter extends DefaultWriter
{

    public function write()
    {
        $this->checkPath();
        $file = new File($this->fileInfo['tmp_name']);

        if($file->copy("{$this->getPath()}{$this->getFileName()}{$this->getFileFormat()}"))
        {
            $this->entity->set($this->field, "{$this->getFileName()}{$this->getFileFormat()}");
            return true;
        }else
        {
            Log::error(__d('upload', 'Unable to save file "{0}" in path "{1}"', $this->getFileName(), $this->getPath()));
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
                Log::error(__d('upload', 'Unable to delete file "{0}" in path "{1}"', $this->getFileName(), $this->getPath()));
            }
        } else
        {
            Log::error(__d('upload', 'Unable to delete file "{0}" in path "{1}" because it does not exist', $this->getFileName(), $this->getPath()));
        }
    }

    private function getFileFormat()
    {
        return '.' . pathinfo($this->fileInfo['name'], PATHINFO_EXTENSION);
    }

}
