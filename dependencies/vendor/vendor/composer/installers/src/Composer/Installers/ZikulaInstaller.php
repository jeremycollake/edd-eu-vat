<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class ZikulaInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$vendor}-{$name}/', 'theme' => 'themes/{$vendor}-{$name}/');
}
