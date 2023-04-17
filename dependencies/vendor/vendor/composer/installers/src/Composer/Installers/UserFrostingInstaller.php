<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class UserFrostingInstaller extends BaseInstaller
{
    protected $locations = array('sprinkle' => 'app/sprinkles/{$name}/');
}
