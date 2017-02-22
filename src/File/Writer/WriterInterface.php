<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Upload\File\Writer;

use Cake\ORM\Table;
use Cake\ORM\Entity;

/**
 *
 * @author allan
 */
interface WriterInterface
{

    public function __construct(Table $table, Entity $entity, $field, $settings);

    public function write();

    public function delete();
}
