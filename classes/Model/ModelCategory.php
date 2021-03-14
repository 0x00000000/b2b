<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Factory\Factory;

/**
 * Model category.
 * 
 * @property string|null $id Id.
 * @property string|null $caption Caption.
 * @property bool $disabled Is category disabled.
 * @property bool $deleted Is category deleted.
 */
class ModelCategory extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'category';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'caption'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'skipControl' => true),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
}
