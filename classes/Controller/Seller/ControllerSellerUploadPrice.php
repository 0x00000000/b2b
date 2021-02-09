<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\System\FileSystem;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

class ControllerSellerUploadPrice extends ControllerSellerBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/seller/price';
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        $action = $this->getFromGet('action');
        
        if ($action === 'savePrice') {
            $content = $this->innerActionSavePrice();
        } else {
            $content = $this->innerActionUploadPrice();
        }
        
        return $content;
    }
    
    protected function innerActionUploadPrice() {
        if ($this->getFromPost('submit')) {
            $this->innerActionDoUploadPrice();
        }
        
        $this->getView()->setTemplate('Seller/uploadPrice');
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoUploadPrice() {
        $success = false;
        if (array_key_exists('priceXls', $this->getRequest()->files)) {
            $tmpFilename = $this->getRequest()->files['priceXls']['tmp_name'];
            if (file_exists($tmpFilename)) {
                $pricePath = $this->getPricePath();
                copy($tmpFilename, $pricePath);
                $priceData = $this->getPriceData($pricePath);
                if ($priceData) {
                    $productsCount = $this->getProductsCount($priceData);
                    if ($productsCount) {
                        $this->clearPriceFromDB();
                        $saved = $this->saveCategoriesToDB($priceData);
                        if (! $saved['error']) {
                            $success = true;
                            $this->getView()->set('productsCount', $productsCount);
                            $this->getView()->set(
                                'productsUploadCount',
                                Config::instance()->get('seller', 'productsUploadCount')
                            );
                        } else {
                            $this->getView()->set('messageType', 'errorSavingToDB');
                        }
                    } else {
                        $this->getView()->set('messageType', 'errorReadingFromXLS');
                    }
                } else {
                    $this->getView()->set('messageType', 'errorReadingFromXLS');
                }
            } else {
                $this->getView()->set('messageType', 'errorUploadingFile');
            }
        } else {
            $this->getView()->set('messageType', 'errorUploadingFile');
        }
        
        return $success;
    }
    
    protected function innerActionSavePrice() {
        $this->setAjaxMode(true);
        
        $pricePath = $this->getPricePath();
        $priceData = $this->getPriceData($pricePath);
        if ($priceData) {
            $from = (int) $this->getFromGet('from');
            $productsUploadCount = Config::instance()->get('seller', 'productsUploadCount');
            $result = $this->saveProductsToDB($priceData, $from, $productsUploadCount);
        } else {
            $result = array('error' => true, 'errorMessage' => 'Can`t read from XLS, check file forman. Error occured.');
        }
        
        $contnt = json_encode($result);
        return $contnt;
    }
    
    protected function getPricePath() {
        $ds = FileSystem::getDS();
        $pricePath = FileSystem::getRoot() . $ds . Config::instance()->get('seller', 'pricePath');
        return $pricePath;
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
    
    protected function getProductsCount(array $priceData): int {
        $count = 0;
        foreach ($priceData as $products) {
            $count += count($products);
        }
        return $count;
    }
    
    protected function clearPriceFromDB() {
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
                    $result['errorMessage'] = 'Error saving category';
                    $result['errorDebugInfo'] = $categoryModel->getLastError();
                    break;
                }
            }
        } else {
            $result['error'] = true;
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
                                $result['errorMessage'] = 'Error finding category ' . $category;
                                break;
                            }
                        }

                        $productData[] = array(
                            'categoryId' => $categoryModel->id,
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
            $result['errorMessage'] = 'Price data is empty';
            $result['debugMessage'] = var_export(array('priceData' => $priceData), true);
        }
        
        return $result;
    }
    
}
