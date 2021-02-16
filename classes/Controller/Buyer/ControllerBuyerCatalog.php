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
        $this->addJsFile('/js/Buyer/catalog.js');
        
        $categoryId = $this->getFromGet('categoryId');
        $page = $this->getFromGet('page');
        
        $categoriesList = Factory::instance()->createModel('Category')->getModelsList(
            array('disabled' => '0', 'deleted' => '0'),
            0, 0, array('id' => 'asc')
        );
        
        $productsConditionsList = array('disabled' => '0', 'deleted' => '0');
        if ((int) $categoryId) {
            $currentCategory = Factory::instance()->createModel('Category')->getOneModel(
                array('id' => (int) $categoryId, 'disabled' => '0', 'deleted' => '0')
            );
            $productsConditionsList['categoryId'] = $currentCategory->id;
        } else {
            $currentCategory = null;
        }
        
        $productsList = Factory::instance()->createModel('Product')->getModelsList(
            $productsConditionsList, 0, 0, array('id' => 'asc')
        );
        
        $order = Factory::instance()->createModel('Order');
        $orderState = $this->getFromSession('orderState');
        if ($orderState) {
            $order->setState($orderState);
        }
        
        $this->getView()->setTemplate('Buyer/catalog');
        $this->getView()->set('categoriesList', $categoriesList);
        $this->getView()->set('currentCategory', $currentCategory);
        $this->getView()->set('productsList', $productsList);
        $this->getView()->set('order', $order);
        $content = $this->getView()->render();
        
        return $content;
    }
    
}
