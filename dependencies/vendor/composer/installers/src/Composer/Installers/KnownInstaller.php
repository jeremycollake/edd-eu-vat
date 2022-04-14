<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class KnownInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'IdnoPlugins/{$name}/', 'theme' => 'Themes/{$name}/', 'console' => 'ConsolePlugins/{$name}/');
}
