<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Model\ModelDatabase;

class ControllerAdminManageSeller extends ControllerAdminManageUser {
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('isSeller' => true, 'isAdmin' => false, 'deleted' => false,);
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/admin/seller';
    
    protected function setPropertiesFromPost(ModelDatabase $model): bool {
        $result = parent::setPropertiesFromPost($model);
        
        if ($result) {
            $model->isAdmin = false;
            $model->isSeller = true;
            $model->isBuyer = false;
        }
        
        return $result;
    }
    
}
