<?php

declare(strict_types=1);

namespace B2bShop\Module\XlsPriceLoader;

/**
 * Xls price loader class.
 */
abstract class XlsPriceLoaderAbstract {
    
    /**
     * Error codes.
     */
    const ERROR_CODE_READING_FROM_XLS = 1;
    const ERROR_CODE_SAVING_TO_DB = 2;
    
    /**
     * Clears products and categories from DB, saves categories to DB.
     */
    abstract public function savingStart(string $pricePath): array;
    
    /**
     * Saves products to DB.
     */
    abstract public function savingProcess($pricePath, $from, $productsUploadCount): array;
    
}
