<?php

namespace Barn2\Plugin\EDD_VAT\Dependencies\Lib;

/**
 * A trait for a service container.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   2.0
 */
trait Service_Container
{
    /**
     * A list of core services that are always required and registered when the plugin is first loaded.
     *
     * @var array
     */
    private $core_services = [];
    /**
     * A list of optional services registered during the plugin bootstrap process, typically when a specific hook is fired.
     *
     * @var array
     */
    private $services = [];
    public function register_core_services()
    {
        Util::register_services($this->core_services);
    }
    public function register_services()
    {
        Util::register_services($this->services);
    }
    public function get_services()
    {
        return \array_merge($this->core_services, $this->services);
    }
    public function get_service($id)
    {
        $services = $this->get_services();
        return $services[$id] ?? null;
    }
    public function add_service($id, $service, $is_core = \false)
    {
        if ($this->valid_service_id($id)) {
            if ($is_core) {
                $this->core_services[$id] = $service;
            } else {
                $this->services[$id] = $service;
            }
        }
    }
    private function valid_service_id($id)
    {
        return !isset($this->services[$id], $this->core_services[$id]);
    }
}
