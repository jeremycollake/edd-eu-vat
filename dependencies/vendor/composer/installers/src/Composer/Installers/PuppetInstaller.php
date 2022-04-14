<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class PuppetInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
