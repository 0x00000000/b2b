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
        'edit' => 'Seller/ManageOrder/edit',
    );
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        if (array_key_exists('action', $get)) {
            $this->_action = $get['action'];
        }
        if (array_key_exists('id', $get)) {
            $this->_id = $get['id'];
        }
        
        if ($this->_action === 'add') {
            $content = '';
            $this->send404();
        } else if ($this->_action === 'export' && $this->_id) {
            $content = $this->innerActionExport();
        } else  {
            $content = parent::actionIndex();
        }
        
        return $content;
    }
    
    protected function getModelControlsList() {
        $controlsList = parent::getModelControlsList();
        $controlsList['products'] = 'none';
        return $controlsList;
    }
    
    protected function innerActionView() {
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $this->getView()->set('products', $item->products);
        }
        
        return parent::innerActionView();
    }
    
    protected function innerActionEdit() {
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $this->getView()->set('products', $item->products);
        }
        
        return parent::innerActionEdit();
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
        if ($filePath && $this->saveXls($order, $filePath)) {
            $this->sendFile($filePath, $order->id);
        }
    }
    
    protected function saveXls($order, $filePath) {
        $errorReportingPrevValue = error_reporting(E_ERROR);
        // Создаем объект класса PHPExcel
        $xls = new \PHPExcel();
        // Устанавливаем индекс активного листа
        $xls->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xls->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('Заказ');
        
        $codesList = [6808, 8827, 9861, 6652, 8753];
        
        $line = 0;
        foreach ($order->products as $code => $product) {
            $line++;
            $sheet->setCellValueByColumnAndRow(
                2,
                $line,
                $code
            );
            $sheet->setCellValueByColumnAndRow(
                3,
                $line,
                $product['caption']
            );
            $sheet->setCellValueByColumnAndRow(
                4,
                $line,
                $product['price']
            );
            $sheet->setCellValueByColumnAndRow(
                5,
                $line,
                $product['count']
            );
        }
        
        $objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        
        $objWriter->save($filePath);
        
        error_reporting($errorReportingPrevValue);
        
        return true;
    }
    
    protected function getOrderPath() {
        $ds = FileSystem::getDS();
        $pricePath = FileSystem::getRoot() . $ds . Config::instance()->get('seller', 'orderPath');
        return $pricePath;
    }
    
    protected function sendFile($filePath, $orderId) {
        $sendName = str_replace('.', '-' . $orderId . '.', basename($filePath));
        header ("Content-Type: application/octet-stream");
        header ("Accept-Ranges: bytes");
        header ("Content-Length: " . filesize($filePath));
        header ("Content-Disposition: attachment; filename=" . $sendName);
        readfile($filePath);
    }
    
}
