<?php

declare(strict_types=1);

namespace B2bShop\Module\Factory;

/**
 * Creates modules and models.
 */
class FactoryAdmin extends FactoryBase {
    
    /**
     * @var string $_moduleNamePostfix Postfix for some modules' names.
     */
    protected $_moduleNamePostfix = 'Admin';
    
    /**
     * Class constructor.
     */
    public function __construct() {
    }
    
}
