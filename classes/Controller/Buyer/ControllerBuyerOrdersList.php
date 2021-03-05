<?php

declare(strict_types=1);

namespace B2bShop\Controller\Buyer;

use B2bShop\System\FileSystem;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

class ControllerBuyerOrdersList extends ControllerBuyerManageBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/history';
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'Order';
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('deleted' => false, 'disabled' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('id' => 'desc',);
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'list' => 'Buyer/ManageOrder/list',
        'view' => 'Buyer/ManageOrder/view',
    );
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'products' => self::CONTROL_NONE,
    );
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        if (array_key_exists('action', $get)) {
            $this->_action = $get['action'];
        }
        if (array_key_exists('id', $get)) {
            $this->_id = $get['id'];
        }
        
        $this->_conditionsList['userId'] = $this->getAuth()->getUser()->id;
        $this->_itemsPerPage = Config::instance()->get('buyer', 'itemsPerPage');
        
        if ($this->_action === 'view') {
            $content = $this->innerActionView();
        } else  {
            $content = $this->innerActionList();
        }
        
        return $content;
    }
    
}
