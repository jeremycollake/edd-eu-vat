<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class PortoInstaller extends BaseInstaller
{
    protected $locations = array('container' => 'app/Containers/{$name}/');
}
