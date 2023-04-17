<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class CodeIgniterInstaller extends BaseInstaller
{
    protected $locations = array('library' => 'application/libraries/{$name}/', 'third-party' => 'application/third_party/{$name}/', 'module' => 'application/modules/{$name}/');
}
