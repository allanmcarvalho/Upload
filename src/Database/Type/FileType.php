<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Database\Type;

/**
 * Description of FileType
 *
 * @author allan
 */
use Cake\Database\Type;
use Cake\Database\Exception;

class FileType extends Type
{

    /**
     * Marshalls flat data into PHP objects.
     *
     * Most useful for converting request data into PHP objects
     * that make sense for the rest of the ORM/Database layers.
     *
     * @param mixed $value The value to convert.
     * @return mixed Converted value.
     */
    public function marshal($value)
    {
        if (!is_array($value))
        {
            throw new Exception(__d('upload', "Misconfigured form"));
        } else
        {
            $mustHave = [
                'tmp_name',
                'error',
                'name',
                'type',
                'size'
            ];
            foreach ($value as $key => $content)
            {
                if (!in_array($key, $mustHave))
                {
                    throw new Exception(__d('upload', "Misconfigured form"));
                }
            }
        }
        return $value;
    }

}
