<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Factory\Factory;

/**
 * Model order.
 * 
 * @property string|null $id Id.
 * @property string|null $productCodesList Product codes list.
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
        array('name' => 'name'),
        array('name' => 'surname'),
        array('name' => 'patronymic'),
        array('name' => 'phone'),
        array('name' => 'email'),
        array('name' => 'address'),
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
        $count = 0;
        foreach ($this->products as $productData) {
            $count += $productData['count'];
        }
        
        return $count;
    }
    
    public function getProductsCost(): float {
        $cost = 0;
        foreach ($this->products as $productData) {
            $cost += $productData['cost'];
        }
        
        return round($cost, 2);
    }
    
    public function addProduct($product, $count = 1) {
        if ($product && $count > 0) {
            $products = $this->products;
            if (! array_key_exists($product->code, $products)) {
                $products[$product->code] = array(
                    'caption' => $product->caption,
                    'cost' => round($product->cost, 2),
                    'link' => $product->link,
                    'price' => $product->price,
                    'cost' => $count * $product->price,
                    'count' => $count,
                );
            } else {
                $products[$product->code]['count'] += $count;
                $products[$product->code]['cost'] += $count * $products[$product->code]['price'];
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
                    $products[$code]['cost'] -= $count * $products[$code]['price'];
                    if ($products[$code]['count'] <= 0) {
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
    
}
