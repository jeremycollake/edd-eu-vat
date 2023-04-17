<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class AttogramInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$name}/');
}
