<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class ElggInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'mod/{$name}/');
}
