<?php

declare(strict_types=1);

namespace B2bShop\Model;

/**
 * Model product.
 * 
 * @property string|null $id Id.
 * @property string|null $categoryId Category id. 
 * @property string|null $code Code.
 * @property string|null $caption Caption.
 * @property string|null $price Price.
 * @property string|null $link Link.
 * @property bool $disabled Is product disabled.
 * @property bool $deleted Is product deleted.
 */
class ModelProduct extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'product';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'categoryId', 'type' => self::TYPE_FK, 'fkModelName' => 'Category'),
        array('name' => 'code'),
        array('name' => 'caption'),
        array('name' => 'price'),
        array('name' => 'link'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'skipControl' => true),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
}
