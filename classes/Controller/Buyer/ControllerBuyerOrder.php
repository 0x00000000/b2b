<?php

declare(strict_types=1);

namespace B2bShop\Controller\Buyer;

use B2bShop\Module\Factory\Factory;

class ControllerBuyerOrder extends ControllerBuyerBase {
    
    /**
     * @var array $_innerUrl Inner url to order root page. Should be started from '/'.
     */
    protected $_innerUrl = '/order';
    
    protected function actionIndex() {
        
        if (! is_null($this->getFromPost('comment'))) {
            $this->innerActionDoSaveOrder();
        }
        
        $this->addCssFile('/css/Buyer/order.css');
        $this->addJsFile('/js/Buyer/order.js');
        $this->addJsFile('/js/Buyer/FormSender.js');
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $this->getView()->setTemplate('Buyer/order');
        $this->getView()->set('order', $order);
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoSaveOrder() {
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $id = null;
        if ($order->getProductsCount()) {
            $comment = $this->getFromPost('comment', '');
            $order->userId = $this->getAuth()->getUser()->id;
            $order->comment = $comment;
            $id = $order->save();
            if ($id) {
                $this->setStashData('messageType', 'orderSaved');
            } else {
                $this->setStashData('messageType', 'errorSaveOrderSavingError');
            }
        } else {
            $this->setStashData('messageType', 'errorSaveOrderEmptyBasket');
        }
        
        if ($id) {
            $this->setStashData('orderId', $id);
            $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
            $userOrder->clearState();
            $userOrder->save();
        } else {
            $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
            $userOrder->setState($order->getState());
            $userOrder->save();
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function actionBuy() {
        $this->setAjaxMode(true);
        
        $code = (int) $this->getFromGet('code');
        $count = (int) $this->getFromGet('count');
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        if ($code && $count) {
            if ($count > 0) {
                $product = Factory::instance()->createModel('Product')->getOneModel(array('disabled' => false, 'deleted' => false, 'code' => $code));
                if ($product) {
                    $order->addProduct($product, $count);
                }
            } else {
                $order->removeProduct($code, -$count);
            }
        }
        
        $userOrder->setState($order->getState());
        $userOrder->save();
        
        if ($order->getProductsCount()) {
            $count = ! empty($order->products[$code]['count'])
                ? $order->products[$code]['count']
                : 0;
            $result = array(
                'error' => false,
                'basket' => array(
                    'count' => $order->getProductsCount(),
                    'cost' => $order->getProductsCost(),
                    'changedProduct' => array(
                        'code' => $code,
                        'count' => $count,
                    ),
                ),
            );
        } else {
            $result = array(
                'error' => false,
                'basket' => array(
                    'count' => $order->getProductsCount(),
                    'cost' => $order->getProductsCost(),
                    'order' => $order->getDataAsArray(),
                ),
            );
        }
        return json_encode($result);
    }
    
    protected function actionGet() {
        $this->setAjaxMode(true);
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $result = array(
            'error' => false,
            'basket' => array(
                'count' => $order->getProductsCount(),
                'cost' => $order->getProductsCost(),
                'order' => $order->getDataAsArray(),
            ),
        );
        return json_encode($result);
    }
    
    protected function setContentViewVariables() {
        parent::setContentViewVariables();
        $orderId = $this->popStashData('orderId');
        if ($orderId) {
            $this->getView()->set('orderId', $orderId);
        }
        $missedProductsCount = $this->popStashData('missedProductsCount');
        if ($missedProductsCount) {
            $this->getView()->set('missedProductsCount', $missedProductsCount);
        }
    }
    
    protected function actionRepeat() {
        $orderId = $this->getFromGet('orderId');
        $prevOrder = Factory::instance()->createModel('Order')->getOneModel(array('orderId' => $orderId));
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $report = $order->join($prevOrder);
        
        $userOrder->setState($order->getState());
        $userOrder->save();
        
        if ($report && $report['missedProductsCount']) {
            $this->setStashData('messageType', 'orderRepeatedMissedProducts');
            $this->setStashData('missedProductsCount', $report['missedProductsCount']);
        } else if ($report && ! $report['error']) {
            $this->setStashData('messageType', 'orderRepeated');
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function actionClear() {
        $this->setAjaxMode(true);
        
        $order = Factory::instance()->createModel('Order');
        $userOrder = Factory::instance()->createModel('UserOrder')->getForUser($this->getAuth()->getUser());
        $order->setState($userOrder->getState());
        
        $order->products = array();
        
        $userOrder->setState($order->getState());
        $userOrder->save();
        
        
        $result = array(
            'error' => false,
            'basket' => array(
                'count' => $order->getProductsCount(),
                'cost' => $order->getProductsCost(),
                'order' => $order->getDataAsArray(),
            ),
        );
        return json_encode($result);
    }
    
}
