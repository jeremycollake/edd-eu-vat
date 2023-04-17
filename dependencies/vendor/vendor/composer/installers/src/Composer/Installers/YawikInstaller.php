<?php

/**
 * Created by PhpStorm.
 * User: cbleek
 * Date: 25.03.16
 * Time: 20:55
 */
namespace Barn2\Plugin\EDD_VAT\Dependencies\Composer\Installers;

class YawikInstaller extends BaseInstaller
{
    protected $locations = array('module' => 'module/{$name}/');
    /**
     * Format package name to CamelCase
     * @param array $vars
     *
     * @return array
     */
    public function inflectPackageVars($vars)
    {
        $vars['name'] = \strtolower(\preg_replace('/(?<=\\w)([A-Z])/', 'Barn2\\Plugin\\EDD_VAT\\Dependencies\\_\\1', $vars['name']));
        $vars['name'] = \str_replace(array('-', '_'), ' ', $vars['name']);
        $vars['name'] = \str_replace(' ', '', \ucwords($vars['name']));
        return $vars;
    }
}
