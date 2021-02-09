<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

class ControllerSellerIndex extends ControllerSellerBase {
    
    protected function actionIndex() {
        $this->redirect($this->getRootUrl() . '/seller/order');
    }
    
}
