<?php

declare(strict_types=1);

namespace B2bShop\Module\Registry;

/**
 * Allow set variables and then get them back.
 */
class Registry {
    
    /**
     * @var array $_data Stores setted data.
     */
    protected static $_data = array();
    
    /**
     * Gets variable by name.
     */
    public static function get(string $name) {
        $result = null;
        
        if (array_key_exists($name, self::$_data)) {
            $result = self::$_data[$name];
        }
        
        return $result;
    }
    
    /**
     * Sets variable for name. If variable is set, it can't be overwritten.
     */
    public static function set(string $name, $object): bool {
        $result = false;
        
        if (! array_key_exists($name, self::$_data)) {
            self::$_data[$name] = $object;
            $result = true;
        }
        
        return $result;
    }
    
}
