<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class MakoInstaller extends BaseInstaller
{
    protected $locations = array('package' => 'app/packages/{$name}/');
}
