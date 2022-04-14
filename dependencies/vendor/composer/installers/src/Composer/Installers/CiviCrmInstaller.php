<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class CiviCrmInstaller extends BaseInstaller
{
    protected $locations = array('ext' => 'ext/{$name}/');
}
