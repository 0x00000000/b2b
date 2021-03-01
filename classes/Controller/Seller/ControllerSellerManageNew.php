<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\Model\ModelDatabase;

class ControllerSellerManageNew extends ControllerSellerManageBuyer {
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('isNew' => true, 'isBuyer' => true, 'isSeller' => false, 'isAdmin' => false, 'deleted' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('disabled' => 'asc', 'id' => 'desc');
    
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
        'isNew' => self::CONTROL_NONE,
        'deleted' => self::CONTROL_NONE,
    );
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/seller/new';
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'add' => 'Common/ManageBase/add',
        'list' => 'Common/ManageBase/list',
        'view' => 'Common/ManageBase/view',
        'edit' => 'Common/ManageBase/edit',
    );
    
}
