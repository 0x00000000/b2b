<?php

declare(strict_types=1);

namespace B2bShop\Module\XlsOrderSaver;

use B2bShop\Model\ModelOrder;

/**
 * Xls order saver class.
 */
abstract class XlsOrderSaverAbstract {
    
    /**
     * Saves order as xls file.
     */
    abstract public function saveXls(ModelOrder $order, string $filePath);
    
}
