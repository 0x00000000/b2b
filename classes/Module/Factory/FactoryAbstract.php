<?php

declare(strict_types=1);

namespace B2bShop\Module\Factory;

use B2bShop\Module\Application\Application;
use B2bShop\Module\Auth\Auth;
use B2bShop\Module\Config\Config;
use B2bShop\Module\Database\Database;
use B2bShop\Module\Factory\Factory;
use B2bShop\Module\Logger\Logger;
use B2bShop\Module\Registry\Registry;
use B2bShop\Module\Request\Request;
use B2bShop\Module\Response\Response;
use B2bShop\Module\Router\Router;
use B2bShop\Module\View\View;

use B2bShop\Model\Model;

use B2bShop\Controller\Controller;

/**
 * Creates modules and models.
 */
abstract class FactoryAbstract {
    
    /**
     * Sets factory module name postrix for singleton object.
     */
    abstract public static function setType(string $type): bool;
    
    /**
     * Gets factory singleton object.
     */
    abstract public static function instance(): Factory;
    
    /**
     * Turns on test mode.
     */
    abstract public function setTestMode(): void;
    
    /**
     * Sets database object.
     */
    abstract public function setDatabase(Database $database): bool;
    
    /**
     * Sets database object.
     */
    abstract protected function getDatabase(): ?Database;
    
    /**
     * Loads controller. Includes it's file, bun doesn't create an object.
     * 
     * @param string $name Controller's name.
     * @return bool Is controller was succsessfully loaded.
     */
    abstract public function loadController(string $name, string $localPath = null): bool;
    
    /**
     * Loads model. Includes it's file, bun doesn't create an object.
     * 
     * @param string $name Model's name.
     * @return bool Is model was succsessfully loaded.
     */
    abstract public function loadModel(string $name): bool;
    
    /**
     * Loads module. Includes it's file, bun doesn't create an object.
     * 
     * @param string $name Module's name.
     * @param string|null $localPath Module section name.
     * @return bool Is module was succsessfully loaded.
     */
    abstract public function loadModule(string $name, string $localPath = null): bool;
    
    /**
     * Loads smarty.
     * 
     * @return bool Is smarty was found.
     */
    abstract public function loadSmarty(): bool;
    
    /**
     * Creates module object.
     */
    abstract public function createModule(string $moduleName, string $moduleBaseName = null): ?object;
    
    /**
     * Creates module object. Module name is calculated from $_moduleNamePostfix property.
     */
    abstract public function createTypedModule(string $moduleBaseName): ?object;
    
    /**
     * Creates model object.
     */
    abstract public function createModel(string $modelName): ?Model;
    
    /**
     * Creates controller object.
     */
    abstract public function createController(string $nameAndPath, Request $request, Response $response): ?Controller;
    
    /**
     * Creates application module object.
     */
    abstract public function createApplication(): Application;
    
    /**
     * Creates auth module object.
     */
    abstract public function createAuth(Request $request): Auth;
    
    /**
     * Creates config module object.
     */
    abstract public function createConfig(): Config;
    
    /**
     * Creates database module object.
     */
    abstract public function createDatabase(): Database;
    
    /**
     * Creates database logger object.
     */
    abstract public function createLogger(Request $request): Logger;
    
    /**
     * Creates registry logger object.
     */
    abstract public function createRegistry(): Registry;
    
    /**
     * Creates request object.
     */
    abstract public function createRequest(): Request;
    
    /**
     * Creates response object.
     */
    abstract public function createResponse(): Response;
    
    /**
     * Creates router logger object.
     */
    abstract public function createRouter(Request $request, Response $response): Router;
    
    /**
     * Creates smarty object.
     */
    abstract public function createSmarty(): ?\Smarty;
    
    /**
     * Creates view object.
     */
    abstract public function createView(): View;
    
}