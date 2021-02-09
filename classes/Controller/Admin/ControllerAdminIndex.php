<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

class ControllerAdminIndex extends ControllerAdminBase {
    
    protected function actionIndex() {
        $this->getView()->setTemplate('Admin/index');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
}
