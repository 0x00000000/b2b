<?php

declare(strict_types=1);

namespace B2bShop\Controller\Buyer;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

class ControllerBuyerCatalog extends ControllerBuyerBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/catalog';
    
    protected function actionCatalog() {
        $this->addCssFile('/css/Buyer/catalog.css');
        $this->addJsFile('/js/Buyer/catalog.js');
        
        $categoryId = $this->getFromGet('categoryId');
        $page = $this->getFromGet('page');
        
        $categoriesList = Factory::instance()->createModel('Category')->getModelsList(
            array('disabled' => '0', 'deleted' => '0'),
            0, 0, array('id' => 'asc')
        );
        
        $productsConditionsList = array('disabled' => false, 'deleted' => false);
        if ((int) $categoryId) {
            $currentCategory = Factory::instance()->createModel('Category')->getOneModel(
                array('id' => (int) $categoryId, 'disabled' => '0', 'deleted' => '0')
            );
            $productsConditionsList['categoryId'] = $currentCategory->id;
        } else {
            $currentCategory = null;
        }
        
        $productsList = Factory::instance()->createModel('Product')->getModelsList(
            $productsConditionsList, 0, 0, array('sort' => 'asc')
        );
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $setting = Factory::instance()->createModel('Setting')->getOneModel(array('name' => 'downloadPrices'));
        $downloadPrices = $setting ? $setting->value : '';
        
        $this->getView()->setTemplate('Buyer/catalog');
        $this->getView()->set('categoriesList', $categoriesList);
        $this->getView()->set('currentCategory', $currentCategory);
        $this->getView()->set('productsList', $productsList);
        $this->getView()->set('order', $order);
        $this->getView()->set('downloadPrices', $downloadPrices);
        $content = $this->getView()->render();
        
        return $content;
    }
    
}
