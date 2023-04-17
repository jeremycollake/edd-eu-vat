<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class ItopInstaller extends BaseInstaller
{
    protected $locations = array('extension' => 'extensions/{$name}/');
}
