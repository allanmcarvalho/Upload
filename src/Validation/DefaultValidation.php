<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Validation;

use Cake\Core\Exception\Exception;
use Upload\Validation\Traits\ImageTrait;
use Upload\Validation\Traits\UploadTrait;

/**
 * Description of UploadValidation
 *
 * @author allancarvalho
 */
class DefaultValidation
{

    use ImageTrait;
    use UploadTrait;

    /**
     * Check if exist file
     * @param array $check
     * @return boolean
     */
    protected static function checkTmpFile($check)
    {
        if (isset($check['tmp_name']))
        {
            if (!is_file($check['tmp_name']))
            {
                return false;
            }
        } else
        {
            return false;
        }
        return true;
    }
    
    /**
     * Verify if file input is a file array
     * @param array $check
     * @throws Exception
     */
    protected static function checkInputType($check)
    {
        if (!is_array($check))
        {
            throw new Exception(__d('upload', "Misconfigured form"));
        }
    }

}
