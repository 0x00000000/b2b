<?php

declare(strict_types=1);

namespace B2bShop\Module\Application;

use B2bShop\Module\Factory\Factory;

/**
 * Facade for other modules.
 * Runs client UI application.
 */
class ApplicationCommon extends ApplicationBase {
    
    /**
     * Runs application.
     */
    public function run(): void {
        
        $request = Factory::instance()->createRequest();
        
        $response = Factory::instance()->createResponse();
        
        $router = Factory::instance()->createRouter($request, $response);
        
        $this->setRoutes($router);
        
        $router->route();
        
    }
    
    public function setRoutes($router): void {
        $router->setRule('/admin', 'Admin/ControllerAdminIndex', 'index');
        
        $router->setRule('/admin/settings[/<action>]', 'Admin/ControllerAdminSettings', 'index');
        
        $router->setRule('/admin/admin[/<action>][/<id>]', 'Admin/ControllerAdminManageAdmin', 'index');
        
        $router->setRule('/admin/seller[/<action>][/<id>]', 'Admin/ControllerAdminManageSeller', 'index');
        
        $router->setRule('/admin/question[/<action>][/<id>]', 'Admin/ControllerAdminManageQuestion', 'index');
        
        $router->setRule('/admin/page[/<action>][/<id>]', 'Admin/ControllerAdminManagePage', 'index');
        
        $router->setRule('/seller', 'Seller/ControllerSellerIndex', 'index');
        
        $router->setRule('/seller/order[/<action>][/<id>]', 'Seller/ControllerSellerOrdersList', 'index');
        
        $router->setRule('/seller/price[/<action>][/<from>]', 'Seller/ControllerSellerUploadPrice', 'index');
        
        $router->setRule('/seller/new[/<action>][/<id>]', 'Seller/ControllerSellerManageNew', 'index');
        
        $router->setRule('/seller/buyer[/<action>][/<id>]', 'Seller/ControllerSellerManageBuyer', 'index');
        
        $router->setRule('/order/buy/<code>/<count>', 'Buyer/ControllerBuyerOrder', 'buy');
        
        $router->setRule('/order/get', 'Buyer/ControllerBuyerOrder', 'get');
        
        $router->setRule('/order', 'Buyer/ControllerBuyerOrder', 'index');
        
        $router->setRule('/catalog[/<categoryId>][/<page>]', 'Buyer/ControllerBuyerCatalog', 'catalog');
        
        $router->setRule('/profile[/<action>]', 'User/ControllerUserProfile', 'index');
        
        $router->setRule('/register', 'User/ControllerUserRegister', 'register');
        
        $router->setRule('/login', 'User/ControllerUserLogin', 'index');
        
        $router->setRule('/logout', 'User/ControllerUserLogin', 'logout');
        
        $router->setRule('/history[/<action>][/<id>]', 'Buyer/ControllerBuyerOrdersList', 'index');
        
        $router->setRule('/', 'Buyer/ControllerBuyerCatalog', 'catalog');
        
        $router->setDefaultRule('ControllerPage', 'index');
        
    }
    
}
