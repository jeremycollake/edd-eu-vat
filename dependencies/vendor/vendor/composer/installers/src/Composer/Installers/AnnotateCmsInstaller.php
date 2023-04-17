<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class AnnotateCmsInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'addons/modules/{$name}/', 'component' => 'addons/components/{$name}/', 'service' => 'addons/services/{$name}/');
}
