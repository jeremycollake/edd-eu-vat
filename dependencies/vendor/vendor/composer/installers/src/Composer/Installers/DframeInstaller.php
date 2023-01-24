<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class DframeInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'modules/{$vendor}/{$name}/');
}
