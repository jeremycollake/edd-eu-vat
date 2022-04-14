<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class DecibelInstaller extends BaseInstaller
{
    /** @var array */
    protected $locations = array('app' => 'app/{$name}/');
}
