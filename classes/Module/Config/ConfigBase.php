<?php

declare(strict_types=1);

namespace B2bShop\Module\Config;

/**
 * Stores configuration data for other modules.
 */
class ConfigBase extends Config {
    /**
     * @var array $_data Stores configuration data.
     */
    protected $_data = array();
    
    /**
     * Class constructor.
     */
    public function __construct() {
    }
    
    /**
     * Gets data from configuration.
     */
    public function get(string $section, string $name = null) {
        $result = null;
        
        if (
            array_key_exists($section, $this->_data)
            && is_array($this->_data[$section])
        ) {
            if (is_null($name)) {
                $result = $this->_data[$section];
            } else {
                if (array_key_exists($name, $this->_data[$section])) {
                    $result = $this->_data[$section][$name];
                } else {
                    $result = null;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Adds data from configuration.
     * Existed data con't be rewrited.
     */
    public function add(string $section, string $name, $value): bool {
        $result = false;

        if ($section && $name) {
            if (
                ! array_key_exists($section, $this->_data)
                || ! is_array($this->_data[$section])
            ) {
                $this->_data[$section] = array();
            }
            
            $this->_data[$section][$name] = $value;
            
            $result = true;
        }
        
        return $result;
    }
    
}
