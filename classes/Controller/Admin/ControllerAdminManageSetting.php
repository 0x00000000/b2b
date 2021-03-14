<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Module\Factory\Factory;

class ControllerAdminManageSetting extends ControllerAdminManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'Setting';
    
    /**
     * @var string $_innerUrl Inner url to controller's root page. Should start from '/'.
     */
    protected $_innerUrl = '/admin/setting';
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'name' => self::CONTROL_LABEL,
        'caption' => self::CONTROL_LABEL,
        'value' => self::CONTROL_TEXTAREA,
        'disabled' => self::CONTROL_NONE,
        'deleted' => self::CONTROL_NONE,
    );
    
    /**
     * Allow user execute actions.
     */
    protected $_canDo = array(
        'add' => false,
        'view' => true,
        'edit' => true,
        'delete' => false,
        'disable' => false,
        'list' => true,
    );
    
}
