<?php

declare(strict_types=1);

namespace B2bShop\Module\View;

/**
 * Renders document.
 */
abstract class ViewAbstract {
    
    /**
     * Sets view template.
     */
    abstract public function setTemplate(string $templatePath): bool;
    
    /**
     * Sets variable.
     */
    abstract public function set(string $key, $value): bool;
    
    /**
     * Renders document.
     */
    abstract public function render(): string;
    
}
