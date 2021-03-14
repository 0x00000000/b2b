<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Factory\Factory;

/**
 * Model order.
 * 
 * @property string|null $id Id.
 * @property string|null $date Order date and time list.
 * @property string|null $products Products data.
 * @property string|null $userId User id.
 * @property Model|null $user User object.
 * @property string|null $comment Comment.
 * @property bool $disabled Is product disabled.
 * @property bool $deleted Is product deleted.
 */
class ModelOrder extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'order';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'date'),
        array('name' => 'products'),
        array('name' => 'user'),
        array('name' => 'userId'),
        array('name' => 'comment'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'skipControl' => false),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->products = array();
    }
    
    /**
     * Gets products data.
     */
    public function getProducts(): ?array {
        $products = $this->getRawProperty('products');
        if ($products) {
            $products = unserialize($products);
        }

        return $products;
    }
    
    /**
     * Sets products data.
     */
    public function setProducts(?array $value): void {
        $products = serialize($value);
        $this->setRawProperty('products', $products);
    }
    
    public function getProductsCount(): int {
        return count($this->products);
    }
    
    public function getProductsCost(): string {
        $cost = 0;
        foreach ($this->products as $productData) {
            $cost += $productData['cost'];
        }
        
        return number_format((float) $cost, 2, '.', '');
    }
    
    public function getUser() {
        $user = null;
        if ($this->userId) {
            $conditionsList = array('id' => $this->userId, 'isBuyer' => true, 'deleted' => false);
            $user = Factory::instance()->createModel('Buyer')->getOneModel($conditionsList);
        }
        return $user;
    }
    
    public function setUser($user) {
        if ($user instanceof ModelUser && $user->id && $user->isBuyer) {
            $this->userId = $user->id;
        }
    }
    
    public function addProduct($product, $count = 1) {
        if ($product && $count > 0) {
            $products = $this->products;
            if (! array_key_exists($product->code, $products)) {
                $products[$product->code] = array(
                    'caption' => $product->caption,
                    'link' => $product->link,
                    'price' => $product->price,
                    'cost' => number_format($count * $product->price, 2, '.', ''),
                    'count' => $count,
                );
            } else {
                $products[$product->code]['count'] += $count;
                $cost = $products[$product->code]['count'] * $products[$product->code]['price'];
                $products[$product->code]['cost'] = number_format($cost, 2, '.', '');
            }
            $this->products = $products;
        }
    }
    
    public function removeProduct($code, $count = null) {
        if ($code && $count > 0) {
            $products = $this->products;
            if (array_key_exists($code, $products)) {
                if (! is_null($count)) {
                    $products[$code]['count'] -= $count;
                    if ($products[$code]['count'] > 0) {
                        $cost = $products[$code]['count'] * $products[$code]['price'];
                        $products[$code]['cost'] = number_format($cost, 2, '.', '');
                    } else {
                        unset($products[$code]);
                    }
                } else {
                    unset($products[$code]);
                }
            }
            $this->products = $products;
        }
    }
    
    public function getDataAsArray() {
        $data = array();
        foreach ($this->_propertiesList as $property) {
            $propertyName = $property['name'];
            if (! empty($this->$propertyName)) {
                if ($propertyName !== 'id') {
                    $data[$propertyName] = $this->$propertyName;
                }
            }
        }
        return $data;
    }
    
    public function getState() {
        
        return serialize($this->getDataAsArray());
    }
    
    public function setState($stateData) {
        $data = unserialize($stateData);
        foreach ($this->_propertiesList as $property) {
            $propertyName = $property['name'];
            if (! empty($data[$propertyName])) {
                if ($propertyName !== 'id') {
                    $this->$propertyName = $data[$propertyName];
                }
            }
        }
    }
    
    public function join(?ModelOrder $orderToJoin): array {
        if ($orderToJoin) {
            $missedProductsCount = 0;
            $productModel = Factory::instance()->createModel('Product');
            $products = $this->products;
            foreach ($orderToJoin->products as $code => $productData) {
                if (isset($products[$code])) {
                    if ($productData['count'] > $products[$code]['count']) {
                        $products[$code]['count'] = $productData['count'];
                        $products[$code]['cost'] = number_format($productData['count'] * $products[$code]['price'], 2, '.', '');
                    }
                } else {
                    $productToAdd = $productModel->getOneModel(array('code' => $code, 'deleted' => false, 'disabled' => false));
                    if ($productToAdd) {
                        $products[$productToAdd->code] = array(
                            'caption' => $productToAdd->caption,
                            'link' => $productToAdd->link,
                            'price' => $productToAdd->price,
                            'cost' => number_format($productData['count'] * $productToAdd->price, 2, '.', ''),
                            'count' => $productData['count'],
                        );
                    } else {
                        $missedProductsCount++;
                    }
                }
            }
            $this->products = $products;
            
            $result = array('error' => '0', 'missedProductsCount' => $missedProductsCount);
        } else {
            $result = array('error' => '1');
        }
        
        return $result;
    }
    
}
