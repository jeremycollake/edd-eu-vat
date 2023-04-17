<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class PantheonInstaller extends BaseInstaller
{
    /** @var array<string, string> */
    protected $locations = array('script' => 'web/private/scripts/quicksilver/{$name}', 'module' => 'web/private/scripts/quicksilver/{$name}');
}
