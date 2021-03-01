<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

class ControllerAdminManagePage extends ControllerAdminManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'Page';
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/admin/page';
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'content' => self::CONTROL_HTML,
    );
    
}
