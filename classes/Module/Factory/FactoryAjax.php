<?php

declare(strict_types=1);

namespace B2bShop\Module\Factory;

/**
 * Creates modules and models.
 */
class FactoryAjax extends FactoryBase {
    
    /**
     * @var string $_moduleNamePostfix Postfix for some modules' names.
     */
    protected $_moduleNamePostfix = 'Ajax';
    
    /**
     * Class constructor.
     */
    public function __construct() {
    }
    
}
