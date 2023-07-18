<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class EzPlatformInstaller extends BaseInstaller
{
    protected $locations = array('meta-assets' => 'web/assets/ezplatform/', 'assets' => 'web/assets/ezplatform/{$name}/');
}
