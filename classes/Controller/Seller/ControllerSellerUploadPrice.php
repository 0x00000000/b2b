<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\System\FileSystem;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

class ControllerSellerUploadPrice extends ControllerSellerBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/seller/price';
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        $action = $this->getFromGet('action');
        
        if ($action === 'savePrice') {
            $content = $this->innerActionSavePrice();
        } else {
            $content = $this->innerActionUploadPrice();
        }
        
        return $content;
    }
    
    protected function innerActionUploadPrice() {
        if ($this->getFromPost('submit')) {
            $this->innerActionDoUploadPrice();
        }
        
        $this->addCssFile('/css/Seller/uploadPrice.css');
        
        $this->addJsFile('/js/Seller/uploadPrice.js');
        
        $this->getView()->setTemplate('Seller/uploadPrice');
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoUploadPrice() {
        $success = false;
        if (array_key_exists('priceXls', $this->getRequest()->files)) {
            $tmpFilename = $this->getRequest()->files['priceXls']['tmp_name'];
            if (file_exists($tmpFilename)) {
                $pricePath = $this->getPricePath();
                copy($tmpFilename, $pricePath);
                $result = Factory::instance()->createModule('XlsPriceLoader')->savingStart($pricePath);
                if (! $result['error']) {
                    $success = true;
                    $this->getView()->set('productsCount', $result['productsCount']);
                    $this->getView()->set(
                        'productsUploadCount',
                        Config::instance()->get('seller', 'productsUploadCount')
                    );
                } else {
                    if ($result['errorCode'] === XlsPriceLoader::ERROR_CODE_SAVING_TO_DB) {
                        $this->getView()->set('messageType', 'errorSavingToDB');
                    } if ($result['errorCode'] === XlsPriceLoader::ERROR_CODE_READING_FROM_XLS) {
                        $this->getView()->set('messageType', 'errorReadingFromXLS');
                    } else {
                        $this->getView()->set('messageType', 'errorInternalError');
                    }
                }
            } else {
                $this->getView()->set('messageType', 'errorUploadingFile');
            }
        } else {
            $this->getView()->set('messageType', 'errorUploadingFile');
        }
        
        return $success;
    }
    
    protected function innerActionSavePrice() {
        $this->setAjaxMode(true);
        
        $pricePath = $this->getPricePath();
        $from = (int) $this->getFromGet('from');
        $productsUploadCount = Config::instance()->get('seller', 'productsUploadCount');
        $result = Factory::instance()->createModule('XlsPriceLoader')->savingProcess(
            $pricePath,
            $from,
            $productsUploadCount
        );
        
        $content = json_encode($result);
        return $content;
    }
    
    protected function getPricePath() {
        $ds = FileSystem::getDS();
        $pricePath = FileSystem::getRoot() . $ds . Config::instance()->get('seller', 'pricePath');
        return $pricePath;
    }
    
}
