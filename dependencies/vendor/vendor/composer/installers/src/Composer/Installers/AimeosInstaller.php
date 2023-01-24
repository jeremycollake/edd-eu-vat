<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class AimeosInstaller extends BaseInstaller
{
    protected $locations = array('extension' => 'ext/{$name}/');
}
