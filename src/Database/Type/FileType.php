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

use Cake\Database\Driver;
use Cake\Database\Exception;
use Cake\Database\Type;
use Cake\Database\TypeInterface;

class FileType extends Type implements TypeInterface
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

    /**
     * Casts given value from a PHP type to one acceptable by a database.
     *
     * @param mixed $value Value to be converted to a database equivalent.
     * @param \Cake\Database\Driver $driver Object from which database preferences and configuration will be extracted.
     * @return mixed Given PHP type casted to one acceptable by a database.
     */
    public function toDatabase($value, Driver $driver)
    {
        return $value;
    }

    /**
     * Casts given value from a database type to a PHP equivalent.
     *
     * @param mixed $value Value to be converted to PHP equivalent
     * @param \Cake\Database\Driver $driver Object from which database preferences and configuration will be extracted
     * @return mixed Given value casted from a database to a PHP equivalent.
     */
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }

        return (string)$value;
    }
}
