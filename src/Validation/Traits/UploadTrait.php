<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Validation\Traits;

use Cake\Utility\Hash;

/**
 * Description of UploadTrait
 *
 * @author allan
 */
trait UploadTrait
{

    /**
     * Check that the file does not exceed the max
     * file size specified by PHP
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderPhpSizeLimit($check)
    {
        self::checkInputType($check);
        return Hash::get($check, 'error') !== UPLOAD_ERR_INI_SIZE;
    }

    /**
     * Check that the file does not exceed the max
     * file size specified in the HTML Form
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isUnderFormSizeLimit($check)
    {
        self::checkInputType($check);
        return Hash::get($check, 'error') !== UPLOAD_ERR_FORM_SIZE;
    }

    /**
     * Check that the file was completely uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isCompletedUpload($check)
    {
        self::checkInputType($check);
        return Hash::get($check, 'error') !== UPLOAD_ERR_PARTIAL;
    }

    /**
     * Check that a file was uploaded
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isFileUpload($check)
    {
        self::checkInputType($check);
        return Hash::get($check, 'error') !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Check that the file was successfully written to the server
     *
     * @param mixed $check Value to check
     * @return bool Success
     */
    public static function isSuccessfulWrite($check)
    {
        self::checkInputType($check);
        return Hash::get($check, 'error') !== UPLOAD_ERR_CANT_WRITE;
    }

    /**
     * Check that the file is above the minimum file upload size
     *
     * @param mixed $check Value to check
     * @param int $size Minimum file size
     * @return bool Success
     */
    public static function isAboveMinSize($check, $size)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        return $check['size'] >= $size;
    }

    /**
     * Check that the file is below the maximum file upload size
     *
     * @param mixed $check Value to check
     * @param int $size Maximum file size
     * @return bool Success
     */
    public static function isBelowMaxSize($check, $size)
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }
        return $check['size'] <= $size;
    }

    /**
     * Check the file mime type
     * @param array $check
     * @param mixed $mimeTypes
     * @return boolean
     */
    public static function isThisMimeType($check, $mimeTypes = [])
    {
        self::checkInputType($check);
        if (!self::checkTmpFile($check))
        {
            return false;
        }

        if (Hash::get($check, 'type', false) === false)
        {
            return false;
        }
        $fileMimeType = Hash::get($check, 'type');

        if (is_array($mimeTypes))
        {
            foreach ($mimeTypes as $mimeType)
            {
                if (!preg_match('/^[-\w]+\/[-\w]+$/', $mimeType))
                {
                    return false;
                }
            }
            if (in_array($fileMimeType, $mimeTypes))
            {
                return true;
            } else
            {
                return false;
            }
        } elseif (is_string($mimeTypes))
        {
            if (!preg_match('/^[-\w]+\/[-\w]+$/', $mimeTypes))
            {
                return false;
            }
            return $fileMimeType === $mimeTypes ? true : false;
        }
        return false;
    }

}
