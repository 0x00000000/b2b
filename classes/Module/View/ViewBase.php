<?php

declare(strict_types=1);

namespace B2bShop\Module\View;

use B2bShop\System\FileSystem;

use B2bShop\Module\Factory\Factory;

/**
 * Routes UA.
 */
abstract class ViewBase extends View {
    
    /**
     * @var string $_templatePath Template full path.
     */
    protected $_templatePath = null;
    
    /**
     * Template variables.
     */
    protected $_variables = array();
    
    /**
     * Class constructor.
     */
    public function __construct() {
        
    }
    
    /**
     * Sets view template.
     */
    public function setTemplate(string $templatePath): bool {
        $result = false;
        
        $ds = FileSystem::getDS();
        $templateFullPath = realpath(FileSystem::getRoot() . $ds . 'template' . $ds . $templatePath . '.tmpl');
        if ($templateFullPath && is_file($templateFullPath)) {
            $this->_templatePath = $templateFullPath;
            $result = true;
        }
        
        if (! $result) {
            $this->_templatePath = null;
        }
        return $result;
    }
    
    /**
     * Sets variable.
     */
    public function set(string $key, $value): bool {
        $result = true;
        
        if (strlen($key)) {
            $this->_variables[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Renders document.
     */
    public function render(): string {
        $content = '';
        
        if ($this->_templatePath) {
            $smarty = Factory::instance()->createSmarty();
            if ($smarty) {
                if (is_array($this->_variables)) {
                    foreach ($this->_variables as $name => $value) {
                        $smarty->assign($name, $value);
                    }
                }
                
                $content = $smarty->fetch($this->_templatePath);
            }
        }
        
        return $content;
    }
    
}
