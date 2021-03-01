<?php

declare(strict_types=1);

namespace B2bShop\Module\XlsPriceLoader;

use B2bShop\Module\Factory\Factory;

/**
 * Xls price loader class.
 */
class XlsPriceLoader extends XlsPriceLoaderAbstract{
    
    /**
     * Clears products and categories from DB, saves categories to DB.
     */
    public function savingStart(string $pricePath): array {
        $error = false;
        $errorCode = null;
        $productsCount = null;
        $priceData = $this->getPriceData($pricePath);
        if ($priceData) {
            $productsCount = $this->getProductsCount($priceData);
            if ($productsCount) {
                $this->clearPriceFromDB();
                $saved = $this->saveCategoriesToDB($priceData);
                if (! $saved['error']) {
                    $error = false;
                } else {
                    $error = true;
                    $errorCode = self::ERROR_CODE_SAVING_TO_DB;
                }
            } else {
                $error = true;
                $errorCode = self::ERROR_CODE_READING_FROM_XLS;
            }
        } else {
            $error = true;
            $errorCode = self::ERROR_CODE_READING_FROM_XLS;
        }
        
        if ($error) {
            $result = array('error' => $error, 'errorCode' => $errorCode);
        } else {
            $result = array('error' => $error, 'productsCount' => $productsCount);
        }
        
        return $result;
    }
    
    /**
     * Saves products to DB.
     */
    public function savingProcess($pricePath, $from, $productsUploadCount):array {
        $priceData = $this->getPriceData($pricePath);
        
        if ($priceData) {
            $result = $this->saveProductsToDB($priceData, $from, $productsUploadCount);
        } else {
            $result = array('error' => true, 'errorCode' => self::ERROR_CODE_READING_FROM_XLS);
        }
        
        return $result;
    }
    
    protected function getProductsCount(array $priceData): int {
        $count = 0;
        foreach ($priceData as $products) {
            $count += count($products);
        }
        return $count;
    }
    
    protected function getPriceData(string $filename): array {
        $priceData = array();
        
        $errorReportingPrevValue = error_reporting(E_ERROR);
        $excel = \PHPExcel_IOFactory::load($filename);
        error_reporting($errorReportingPrevValue);
        
        foreach ($excel->getWorksheetIterator() as $worksheet) {
            $lists[] = $worksheet->toArray();
        }
        
        $category = '';
        $products = array();
        foreach($lists[0] as $key => $row) {
            if (is_null($row[2]) && $row[3]) {
                if ($category && $products) {
                    $priceData[$category] = $products;
                    $products = array();
                }
                $category = $row[3];
            } else if ($row[2] && $row[3] && $row[4]) {
                $products[] = array(
                    'code' => $row[2],
                    'caption' => $row[3],
                    'price' => $row[4],
                    'link' => $row[6],
                );
            }
        }
        if ($category && $products) {
            $priceData[$category] = $products;
            $category = '';
            $products = array();
        }
        
        return $priceData;
    }
    
    public function clearPriceFromDB() {
        $categoryModel = Factory::instance()->createModel('Category');
        $productModel = Factory::instance()->createModel('Product');
        $categoryModel->delete(array('disabled' => '0'));
        $productModel->delete(array('disabled' => '0'));
    }
    
    protected function saveCategoriesToDB(array $priceData): array {
        $result = array('error' => false);
        if ($priceData) {
            $productModel = Factory::instance()->createModel('Product');
            foreach ($priceData as $category => $products) {
                $categoryModel = Factory::instance()->createModel('Category');
                $categoryModel->caption = $category;
                $saved = $categoryModel->save();
                if (! $saved) {
                    $result['error'] = true;
                    $result['errorCode'] = self::ERROR_CODE_SAVING_TO_DB;
                    $result['errorMessage'] = 'Error saving category';
                    $result['errorDebugInfo'] = $categoryModel->getLastError();
                    break;
                }
            }
        } else {
            $result['error'] = true;
            $result['errorCode'] = self::ERROR_CODE_SAVING_TO_DB;
            $result['errorMessage'] = 'Price data is empty';
            $result['debugMessage'] = var_export(array('priceData' => $priceData), true);
        }
        
        return $result;
    }
    
    protected function saveProductsToDB(array $priceData, int $from = 1, int $count = 500): array {
        $result = array('error' => false);
        if ($priceData) {
            $to = $from + $count - 1;
            $counter = 0;
            $productModel = Factory::instance()->createModel('Product');
            foreach ($priceData as $category => $products) {
                if ($category && $products) {
                    $categoryModel = null;
                    $productData = array();
                    foreach ($products as $product) {
                        $counter++;
                        if ($counter < $from)
                            continue;
                        if ($counter > $to) {
                            $result['finished'] = false;
                            break;
                        }
                        
                        if (empty($categoryModel)) {
                            $categoryModel = Factory::instance()->createModel('Category')->getOneModel(array('caption' => $category, 'disabled' => '0', 'deleted' => '0'));
                            if (empty($categoryModel)) {
                                $result['error'] = true;
                                $result['errorCode'] = self::ERROR_CODE_SAVING_TO_DB;
                                $result['errorMessage'] = 'Error finding category ' . $category;
                                break;
                            }
                        }

                        $productData[] = array(
                            'categoryId' => $categoryModel->id,
                            'sort' => $counter,
                            'code' => $product['code'],
                            'caption' => $product['caption'],
                            'price' => $product['price'],
                            'link' => $product['link'],
                        );
                    }
                    if ($result['error']) {
                        break; // Error occured before saving products.
                    }
                    if ($productData) {
                        $saved = $productModel->saveMultiple($productData);
                        if (! $saved) {
                            $result['error'] = true;
                            $result['errorCode'] = self::ERROR_CODE_SAVING_TO_DB;
                            $result['errorMessage'] = 'Error saving products';
                            $result['errorDebugInfo'] = $productModel->getLastError();
                            break;
                        }
                    }
                    if ($result['error']) {
                        break; // Error occured after saving products.
                    }
                    if ($result['error'] === false && isset($result['finished'])) {
                        break;
                    }
                }
            }
            if ($result['error'] === false && ! isset($result['finished'])) {
                $result['finished'] = true;
            }
        } else {
            $result['error'] = true;
            $result['errorCode'] = self::ERROR_CODE_READING_FROM_XLS;
            $result['errorMessage'] = 'Price data is empty';
            $result['debugMessage'] = var_export(array('priceData' => $priceData), true);
        }
        
        return $result;
    }
    
}
