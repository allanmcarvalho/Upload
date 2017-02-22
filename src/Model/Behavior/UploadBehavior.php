<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\Model\Behavior;

use Cake\ORM\Behavior;
use Intervention\Image\Image;

/**
 * CakePHP UploadBehavior
 * @author allancarvalho
 */
class UploadBehavior extends Behavior
{

    public function initialize(array $config)
    {
        parent::initialize($config);
    }

}
