<?php

declare(strict_types=1);

namespace B2bShop\Module\Config;

/**
 * Stores configuration data for other modules.
 */
abstract class ConfigAbstract {
    
    /**
     * Gets data from configuration.
     */
    abstract public function get(string $section, string $name = null);
    
    /**
     * Adds data from configuration.
     * Existed data con't be rewrited.
     */
    abstract public function add(string $section, string $name, $value): bool;
    
}