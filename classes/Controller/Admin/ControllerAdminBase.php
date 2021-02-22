<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Controller\ControllerBase;

abstract class ControllerAdminBase extends ControllerBase {
    
    /**
     * Execute controller action.
     */
    public function execute(string $action): void {
        if (! $this->getAuth() || ! $this->getAuth()->isAdmin()) {
            $this->redirect($this->getAuthUrl());
        }
        
        parent::execute($action);
    }
    
    /** 
     * Adds common varibles to page's view.
     */
    protected function setPageViewVariables(): void {
        parent::setPageViewVariables();
        
        $this->getPageView()->set('bodyClass', 'admin');
    }
    
}
