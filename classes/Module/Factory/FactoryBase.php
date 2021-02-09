<?php

declare(strict_types=1);

namespace B2bShop\Module\Factory;

use B2bShop\System\Core;
use B2bShop\System\FileSystem;

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
use B2bShop\Model\ModelDatabase;

use B2bShop\Controller\Controller;

/**
 * Creates modules and models.
 */
abstract class FactoryBase extends Factory {
    
    /**
     * @var string $_moduleNamePostfix Postfix for some modules' names.
     */
    protected $_moduleNamePostfix = null;
    
    /**
     * @var bool $_isTestMode If test mode is turned on.
     */
    protected $_isTestMode = false;
    
    /**
     * @var Database $_database Database object.
     */
    protected $_database = null;
    
    /**
     * Turns on test mode.
     */
    public function setTestMode(): void {
        $this->_isTestMode = true;
    }
    
    /**
     * Checks if test mode is turned on.
     */
    protected function isTestMode(): bool {
        return $this->_isTestMode;
    }
    
    /**
     * Sets database object.
     */
    public function setDatabase(Database $database): bool {
        $result = false;
        
        if (is_object($database) && $database instanceof Database) {
            $this->_database = $database;
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Gets database object.
     */
    protected function getDatabase(): ?Database {
        return $this->_database;
    }
    
    /**
     * Loads controller. Includes it's file, bun doesn't create an object.
     * 
     * @param string $name Controller's name.
     * @return bool Is controller was succsessfully loaded.
     */
    public function loadController(string $name, string $localPath = null): bool {
        if (! empty($localPath)) {
            $path = 'Controller/' . $localPath;
        } else {
            $path = 'Controller';
        }
        $result = Core::loadClass($name, $path);
        
        return $result;
    }
    
    /**
     * Loads model. Includes it's file, bun doesn't create an object.
     * 
     * @param string $modelName Model's name.
     * @return bool Is model was succsessfully loaded.
     */
    public function loadModel(string $modelName): bool {
        $modelClassName = 'Model' . $modelName;
        $result = Core::loadClass($modelClassName, 'Model');
        
        return $result;
    }
    
    /**
     * Loads module. Includes it's file, bun doesn't create an object.
     * 
     * @param string $name Module's name.
     * @param string|null $localPath Module section name.
     * @return bool Is module was succsessfully loaded.
     */
    public function loadModule(string $name, string $localPath = null): bool {
        $result = false;
        
        if ($name) {
            if (! $localPath) {
                $localPath = $name;
            }
            
            $path = 'Module/' . $localPath;
            $result = Core::loadClass($name, $path);
        }
        
        return $result;
    }
    
    /**
     * Loads smarty.
     * 
     * @return bool Is smarty was found.
     */
    public function loadSmarty(): bool {
        $result = false;
        
        $ds = FileSystem::getDS();
        $path = FileSystem::getRoot() . $ds . 'vendor' . $ds . 'smarty' . $ds . 'libs' . $ds . 'Smarty.class.php';
        if (is_file($path)) {
            include_once($path);
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Creates module object.
     */
    public function createModule(string $moduleName, string $moduleBaseName = null): ?object {
        $result = null;
        
        $className = Core::getModuleClassName($moduleName, $moduleBaseName);
        if (class_exists($className)) {
            $result = new $className();
        }
        
        return $result;
    }
    
    /**
     * Creates module object. Module name is calculated from $_moduleNamePostfix property.
     */
    public function createTypedModule(string $moduleBaseName): ?object {
        $result = null;
        
        if ($moduleBaseName) {
            $moduleName = $moduleBaseName . $this->_moduleNamePostfix;
            $result = $this->createModule($moduleName, $moduleBaseName);
        }
        
        return $result;
    }
    
    /**
     * Creates model object.
     */
    public function createModel(string $modelName): ?Model {
        $model = null;
        
        $className = Core::getModelClassName($modelName);
        if (class_exists($className)) {
            $model = new $className();
            
            if ($model instanceof ModelDatabase) {
                if ($this->getDatabase()) {
                    $model->setDatabase($this->getDatabase());
                }
            }
        }
        
        return $model;
    }
    
    /**
     * Creates controller object.
     */
    public function createController(string $controllerPostfix, Request $request, Response $response): ?Controller {
        $result = null;
        
        $className = Core::getControllerClassName($controllerPostfix);
        if ($className) {
            $result = new $className();
            if ($result instanceof Controller) {
                $result->init($request, $response);
            }
        }
        
        return $result;
    }
    
    /**
     * Creates application module object.
     */
    public function createApplication(): Application {
        $moduleBaseName = 'Application';
        if (! $this->isTestMode()) {
            $moduleName = $moduleBaseName . 'Common';
        } else {
            $moduleName = $moduleBaseName . 'Test';
        }
        $object = $this->createModule($moduleName, $moduleBaseName);
        return $object;
    }
    
    /**
     * Creates auth module object.
     */
    public function createAuth(Request $request): Auth {
        $moduleBaseName = 'Auth';
        if (! $this->isTestMode()) {
            $moduleName = $moduleBaseName . 'Common';
        } else {
            $moduleName = $moduleBaseName . 'Test';
        }
        $object = $this->createModule($moduleName, $moduleBaseName);
        $object->setRequest($request);
        return $object;
    }
    
    /**
     * Creates config module object.
     */
    public function createConfig(): Config {
        
        $this->loadModule('Config');
        $type = 'Common';
        Config::setType($type);
        $object = Config::instance();
        return $object;
    }
    
    /**
     * Creates database module object.
     */
    public function createDatabase(): Database {
        $moduleBaseName = 'Database';
        if (! $this->isTestMode()) {
            $moduleName = $moduleBaseName . 'Mysql';
        } else {
            $moduleName = $moduleBaseName . 'Test';
        }
        $object = $this->createModule($moduleName, $moduleBaseName);
        return $object;
    }
    
    /**
     * Creates database logger object.
     */
    public function createLogger(Request $request): Logger {
        $moduleName = 'Logger';
        $object = $this->createModule($moduleName );
        $object->setRequest($request);
        return $object;
    }
    
    /**
     * Creates registry logger object.
     */
    public function createRegistry(): Registry {
        $moduleName = 'Registry';
        $object = $this->createModule($moduleName);
        return $object;
    }
    
    /**
     * Creates request object.
     */
    public function createRequest(): Request {
        $moduleBaseName = 'Request';
        if (! $this->isTestMode()) {
            $moduleName = $moduleBaseName . 'Http';
        } else {
            $moduleName = $moduleBaseName . 'Test';
        }
        $object = $this->createModule($moduleName, $moduleBaseName);
        return $object;
    }
    
    /**
     * Creates response object.
     */
    public function createResponse(): Response {
        $moduleBaseName = 'Response';
        $moduleName = $moduleBaseName . 'Http';
        $object = $this->createModule($moduleName, $moduleBaseName);
        return $object;
    }
    
    /**
     * Creates router logger object.
     */
    public function createRouter(Request $request, Response $response): Router {
        $moduleBaseName = 'Router';
        if (! $this->isTestMode()) {
            $moduleName = $moduleBaseName . 'Common';
        } else {
            $moduleName = $moduleBaseName . 'Test';
        }
        $object = $this->createModule($moduleName, $moduleBaseName);
        $object->init($request, $response);
        return $object;
    }
    
    /**
     * Creates smarty object.
     */
    public function createSmarty(): ?\Smarty {
        $result = null;
        
        if ($this->loadSmarty()) {
            $smarty  = new \Smarty();
            
            $ds = FileSystem::getDS();
            $smartyDataDir = FileSystem::getDataDir() . $ds . 'smarty';
            
            $smarty->setTemplateDir($smartyDataDir . $ds . 'templates');
            $smarty->setCompileDir($smartyDataDir . $ds . 'templates_c');
            $smarty->setConfigDir($smartyDataDir . $ds . 'configs');
            $smarty->setCacheDir($smartyDataDir . $ds . 'cache');
            
            $result = $smarty;
        }
        
        return $result;
    }
    
    /**
     * Creates view object.
     */
    public function createView(): View {
        $moduleBaseName = 'View';
        $moduleName = $moduleBaseName . 'Common';
        $object = $this->createModule($moduleName, $moduleBaseName);
        return $object;
    }
    
}
