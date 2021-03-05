<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\System\FileSystem;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

class ControllerSellerOrdersList extends ControllerSellerManageBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/seller/order';
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'Order';
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('deleted' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('disabled' => 'asc', 'id' => 'desc',);
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'list' => 'Seller/ManageOrder/list',
        'view' => 'Seller/ManageOrder/view',
    );
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'products' => self::CONTROL_NONE,
    );
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        
        $this->_itemsPerPage = Config::instance()->get('seller', 'itemsPerPage');
    }
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        if (array_key_exists('action', $get)) {
            $this->_action = $get['action'];
        }
        if (array_key_exists('id', $get)) {
            $this->_id = $get['id'];
        }
        
        if ($this->_action === 'add' || $this->_action === 'edit') {
            $content = '';
            $this->send404();
        } else if ($this->_action === 'export' && $this->_id) {
            $content = $this->innerActionExport();
        } else  {
            $content = parent::actionIndex();
        }
        
        return $content;
    }
    
    protected function innerActionView() {
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $this->getView()->set('order', $item);
        }
        
        return parent::innerActionView();
    }
    
    protected function innerActionExport() {
        $this->innerActionDoExport();
        
        return '';
    }
    
    protected function innerActionDoExport() {
        if ($this->_id) {
            $conditionsList = $this->_conditionsList;
            $conditionsList['id'] = $this->_id;
            $model = Factory::instance()->createModel($this->_modelName);
            $item = $model->getOneModel($conditionsList);
            
            if ($item) {
                $this->printXls($item);
            } else {
                $this->setStashData('messageType', 'itemNotFound');
            }
        } else {
            $this->setStashData('messageType', 'wrongParamethers');
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function printXls($order) {
        $filePath = $this->getOrderPath();
        $saved = Factory::instance()->createModule('XlsOrderSaver')->saveXls(
            $order,
            $filePath
        );
        if ($saved) {
            $this->sendFile($filePath, $order->id);
        }
    }
    
    protected function getOrderPath() {
        $ds = FileSystem::getDS();
        $pricePath = FileSystem::getRoot() . $ds . Config::instance()->get('seller', 'orderPath');
        return $pricePath;
    }
    
    protected function sendFile($filePath, $orderId) {
        if (is_file($filePath) && is_readable($filePath)) {
            $sendName = str_replace('.', '-' . $orderId . '.', basename($filePath));
            header('Content-Type: application/vnd.ms-excel');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: attachment; filename=' . $sendName);
            readfile($filePath);
            exit;
        } else {
            echo 'File is unavailable!';
            exit;
        }
    }
    
}
