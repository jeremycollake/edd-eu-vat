<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class Redaxo5Installer extends BaseInstaller
{
    protected $locations = array('addon' => 'redaxo/src/addons/{$name}/', 'bestyle-plugin' => 'redaxo/src/addons/be_style/plugins/{$name}/');
}
