<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class BonefishInstaller extends BaseInstaller
{
    protected $locations = array('package' => 'Packages/{$vendor}/{$name}/');
}
