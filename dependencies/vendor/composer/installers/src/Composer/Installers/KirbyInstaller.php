<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class KirbyInstaller extends BaseInstaller
{
    protected $locations = array('plugin' => 'site/plugins/{$name}/', 'field' => 'site/fields/{$name}/', 'tag' => 'site/tags/{$name}/');
}
