<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Model\ModelDatabase;

class ControllerAdminManageSeller extends ControllerAdminManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'User';
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('isSeller' => true, 'isAdmin' => false, 'deleted' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('disabled' => 'asc', 'name' => 'asc');
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'password' => self::CONTROL_PASSWORD,
        'isAdmin' => self::CONTROL_NONE,
        'isSeller' => self::CONTROL_NONE,
        'isBuyer' => self::CONTROL_NONE,
        'deleted' => self::CONTROL_NONE,
    );
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/admin/manage/seller';
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'add' => 'Common/ManageBase/add',
        'list' => 'Common/ManageBase/list',
        'view' => 'Common/ManageBase/view',
        'edit' => 'Common/ManageBase/edit',
    );
    
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
