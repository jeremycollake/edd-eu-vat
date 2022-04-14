<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class LavaLiteInstaller extends BaseInstaller
{
    protected $locations = array('package' => 'packages/{$vendor}/{$name}/', 'theme' => 'public/themes/{$name}/');
}
