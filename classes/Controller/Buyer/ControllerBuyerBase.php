<?php

declare(strict_types=1);

namespace B2bShop\Controller\Buyer;

use B2bShop\Controller\ControllerBase;

abstract class ControllerBuyerBase extends ControllerBase {
    
    /**
     * Executes before controller action.
     */
    protected function before(): void {
        if (! $this->getAuth()->isBuyer()) {
            $this->redirect($this->getAuthUrl());
        }
    }
    
    /**
     * Adds common varibles to page's view.
     */
    protected function setPageViewVariables(): void {
        parent::setPageViewVariables();
        
        $this->getPageView()->set('bodyClass', 'buyer');
    }
        
}
    