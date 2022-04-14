<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class SMFInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'Sources/{$name}/', 'theme' => 'Themes/{$name}/');
}
