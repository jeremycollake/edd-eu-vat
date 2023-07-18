<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class WolfCMSInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'wolf/plugins/{$name}/');
}
