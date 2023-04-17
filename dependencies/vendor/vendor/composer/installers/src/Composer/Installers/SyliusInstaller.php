<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class SyliusInstaller extends BaseInstaller
{
    protected $locations = array('theme' => 'themes/{$name}/');
}
