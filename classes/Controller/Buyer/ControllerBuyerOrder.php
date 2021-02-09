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
        
        if ($this->getFromPost('submit')) {
            $this->innerActionDoSaveOrder();
        }
        
        $this->addJsFile('/js/Buyer/order.js');
        
        $order = Factory::instance()->createModel('Order');
        $orderState = $this->getFromSession('orderState');
        if ($orderState) {
            $order->setState($orderState);
        }
        
        $this->getView()->setTemplate('Buyer/order');
        $this->getView()->set('order', $order);
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoSaveOrder() {
        $order = Factory::instance()->createModel('Order');
        $orderState = $this->getFromSession('orderState');
        if ($orderState) {
            $order->setState($orderState);
        }
        
        $id = null;
        if ($order->getProductsCount()) {
            $name = $this->getFromPost('submit');
            $surname = $this->getFromPost('surname');
            $patronymic = $this->getFromPost('patronymic', '');
            $phone = $this->getFromPost('phone');
            $email = $this->getFromPost('email');
            $address = $this->getFromPost('address', '');
            $comment = $this->getFromPost('comment', '');
            if (empty($name)) {
                $this->setStashData('messageType', 'errorSaveOrderEmptyName');
            } else if (empty($surname)) {
                $this->setStashData('messageType', 'errorSaveOrderEmptySurname');
            } else if (empty($phone) && empty($email)) {
                $this->setStashData('messageType', 'errorSaveOrderEmptyPhoneAndEmail');
            } else {
                $order->name = $name;
                $order->surname = $surname;
                $order->patronymic = $patronymic;
                $order->phone = $phone;
                $order->email = $email;
                $order->address = $address;
                $order->comment = $comment;
                $id = $order->save();
                if ($id) {
                    $this->setStashData('messageType', 'orderSaved');
                } else {
                    $this->setStashData('messageType', 'errorSaveOrderSavingError');
                }
            }
        } else {
            $this->setStashData('messageType', 'errorSaveOrderEmptyBasket');
        }
        
        if ($id) {
            $this->setStashData('orderId', $id);
            $this->unsetFromSession('orderState', $order->getState());
        } else {
            $this->setToSession('orderState', $order->getState());
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function actionBuy() {
        $this->setAjaxMode(true);
        
        $code = (int) $this->getFromGet('code');
        $count = (int) $this->getFromGet('count');
        
        $order = Factory::instance()->createModel('Order');
        $orderState = $this->getFromSession('orderState');
        if ($orderState) {
            $order->setState($orderState);
        }
        
        if ($code && $count) {
            $product = Factory::instance()->createModel('Product')->getOneModel(array('disabled' => false, 'deleted' => false, 'code' => $code));
            if ($product) {
                if ($count > 0) {
                    $order->addProduct($product, $count);
                } else {
                    $order->removeProduct($product->code, -$count);
                }
            }
        }
        
        $this->setToSession('orderState', $order->getState());
        
        $result = array(
            'error' => false,
            'basket' => array(
                'count' => $order->getProductsCount(),
                'cost' => $order->getProductsCost(),
                'order' => $order->getDataAsArray(),
            )
        );
        return json_encode($result);
    }
    
    protected function actionGet() {
        $this->setAjaxMode(true);
        
        $order = Factory::instance()->createModel('Order');
        $orderState = $this->getFromSession('orderState');
        if ($orderState) {
            $order->setState($orderState);
        }
        
        $result = array(
            'error' => false,
            'basket' => array(
                'count' => $order->getProductsCount(),
                'cost' => $order->getProductsCost(),
                'order' => $order->getDataAsArray(),
            )
        );
        return json_encode($result);
    }
    
    protected function setContentViewVariables() {
        parent::setContentViewVariables();
        $orderId = $this->popStashData('orderId');
        if ($orderId) {
            $this->getView()->set('orderId', $orderId);
        }
    }
    
}
