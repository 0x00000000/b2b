<?php

declare(strict_types=1);

namespace B2bShop\Module\Application;

/**
 * Facade for other modules.
 * Runs application.
 */
abstract class ApplicationBase extends Application {
    
    /**
     * Class's constructor.
     */
    public function __construct() {
        
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        
        $this->initSession();
        
    }
    
    /**
     * Runs application.
     */
    public function run(): void {
        
    }
    
    /**
     * Initialise sassion.
     */
    protected function initSession(): void {
        
        session_start();
        
    }
    
}
