<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class FuelphpInstaller extends BaseInstaller
{
    protected $locations = array('component' => 'components/{$name}/');
}
