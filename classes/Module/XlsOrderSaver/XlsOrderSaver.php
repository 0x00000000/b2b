<?php

declare(strict_types=1);

namespace B2bShop\Module\XlsOrderSaver;

use B2bShop\Module\Factory\Factory;

use B2bShop\Model\ModelOrder;

/**
 * Xls order saver class.
 */
class XlsOrderSaver extends XlsOrderSaverAbstract{
    
    /**
     * Saves order as xls file.
     */
    public function saveXls(ModelOrder $order, string $filePath) {
        $result = false;
        
        if ($filePath && is_array($order->products) && count($order->products)) {
            $errorReportingPrevValue = error_reporting(E_ERROR);
            
            $xls = new \PHPExcel();
            $xls->setActiveSheetIndex(0);
            $sheet = $xls->getActiveSheet();
            $sheet->setTitle('Order ' . $order->id);
            
            $line = 0; // Sheet's lines started from 1.
            foreach ($order->products as $code => $product) {
                $line++;
                $sheet->setCellValueByColumnAndRow(
                    2,
                    $line,
                    $code
                );
                $sheet->setCellValueByColumnAndRow(
                    3,
                    $line,
                    $product['caption']
                );
                $sheet->setCellValueByColumnAndRow(
                    4,
                    $line,
                    $product['price']
                );
                $sheet->setCellValueByColumnAndRow(
                    5,
                    $line,
                    $product['count']
                );
            }
            
            $objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel5');
            
            $objWriter->save($filePath);
            
            error_reporting($errorReportingPrevValue);
            
            $result = true;
        }
        
        return $result;
    }
    
}
