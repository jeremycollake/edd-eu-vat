<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class MODULEWorkInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
