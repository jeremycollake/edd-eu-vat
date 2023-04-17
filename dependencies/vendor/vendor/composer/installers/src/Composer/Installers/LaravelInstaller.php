<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class LaravelInstaller extends BaseInstaller
{
    protected $locations = array('library' => 'libraries/{$name}/');
}
