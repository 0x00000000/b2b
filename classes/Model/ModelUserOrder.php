<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Factory\Factory;
use B2bShop\ModelUser;

/**
 * Model current user order.
 * 
 * @property string|null $userId User id.
 * @property Model|null $user User object.
 * @property string|null $products Product data.
 */
class ModelUserOrder extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'user_order';
    
    /**
     * @var string $_pkFieldName Primary key field name.
     */
    protected $_pkFieldName = 'user_id';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'userId'),
        array('name' => 'user'),
        array('name' => 'products'),
    );
    
    public function getUser() {
        $user = null;
        if ($this->userId) {
            $conditionsList = array('id' => $this->userId, 'isBuyer' => true, 'deleted' => false);
            $user = Factory::instance()->createModel('Buyer')->getOneModel($conditionsList);
        }
        return $user;
    }
    
    public function setUser($user): void {
        if ($user instanceof ModelUser && $user->id && $user->isBuyer) {
            $this->userId = $user->id;
        }
    }
    
    public function getState(): string {
        $products = $this->products ? unserialize($this->products) : array();
        $data = array('products' => $products);
        return serialize($data);
    }
    
    public function setState(string $stateData): void {
        $products = array();
        
        if ($stateData) {
            $data = unserialize($stateData);
            if ($data && $data['products']) {
                $products = $data['products'];
            }
        }
        
        $this->products = serialize($products);
    }
    
    public function clearState() {
        $this->products = serialize(array());
    }
    
    public function getForUser(Model $user) {
        $userOrder = $this->getOneModel(array('userId' => $user->id));
        if (! $userOrder) {
            $userOrder = Factory::instance()->createModel('UserOrder');
            $userOrder->userId = $user->id;
        }
        return $userOrder;
    }
    
}
