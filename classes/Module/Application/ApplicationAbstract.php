<?php

declare(strict_types=1);

namespace B2bShop\Module\Application;

/**
 * Facade for other modules.
 * Runs application.
 */
abstract class ApplicationAbstract {
    
    /**
     * Runs application.
     */
    abstract public function run(): void;
    
}