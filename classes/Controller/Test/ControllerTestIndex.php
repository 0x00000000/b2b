<?php

declare(strict_types=1);

namespace B2bShop\Controller\Test;

use B2bShop\Controller\ControllerBase;

class ControllerTestIndex extends ControllerBase {
    
    protected function actionFillImages() {
        $body = 'Test contorller.';
        
        return $body;
    }
    
}
