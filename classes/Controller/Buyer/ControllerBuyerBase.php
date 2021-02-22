<?php

declare(strict_types=1);

namespace B2bShop\Controller\Buyer;

use B2bShop\Controller\ControllerBase;

abstract class ControllerBuyerBase extends ControllerBase {
    
    /**
     * Execute controller action.
     */
    public function execute(string $action): void {
        if (! $this->getAuth() || ! $this->getAuth()->isBuyer()) {
            $this->redirect($this->getAuthUrl());
        }
        
        parent::execute($action);
    }
    
    
    /**
     * Adds common varibles to page's view.
     */
    protected function setPageViewVariables(): void {
        parent::setPageViewVariables();
        
        $this->getPageView()->set('bodyClass', 'buyer');
    }
        
}