<?php

declare(strict_types=1);

namespace B2bShop\Module\Config;

/**
 * Stores configuration data for other modules.
 */
class ConfigCommon extends ConfigBase {
    
    /**
     * @var array $_data Stores configuration data.
     */
    protected $_data = array(
        'application' => array(
            // Must be set if application is not in site's root.
            'urlPrefix' => '/b2b/public',
            // Must be set if application is not in site's root.
            'baseUrl' => 'http://test.local/b2b/public',
        ),
        'database' => array(
            'server' => 'localhost',
            'login' => 'mysql',
            'password' => 'localpass',
            'name' => 'test',
            'prefix' => 'b2bshop_',
        ),
        'site' => array(
            'caption' => 'Products catalog',
            'title' => 'Products catalog',
            'keywords' => '',
            'description' => '',
        ),
        'user' => array(
            'salt1' => 'DSneicld3!',
            'salt2' => 'gHjeMzz)$3',
        ),
        'admin' => array(
            'mainPageUrl' => '/admin/manage/seller',
            'itemsPerPage' => 20,
        ),
        'seller' => array(
            'mainPageUrl' => '/seller',
            'itemsPerPage' => 20,
            'productsUploadCount' => 500,
            'pricePath' => 'data/xls/price/price.xls',
            'orderPath' => 'data/xls/order/order.xls',
        ),
        'buyer' => array(
            'mainPageUrl' => '',
            'itemsPerPage' => 20,
        ),
    );
    
}
