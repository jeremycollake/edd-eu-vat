<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class KohanaInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
