<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Auth\Auth;

/**
 * Model with user access property.
 * 
 * @property bool $accessAdmin Access rights for admins.
 * @property bool $accessSeller Access rights for sellers.
 * @property bool $accessBuyer Access rights for buyers.
 * @property bool $accessGuest Access rights for guests.
 */
abstract class ModelAccessRights extends ModelDatabase {
    
    public function userHasAccess(Auth $auth): bool {
        $result = (
            ($auth->isAdmin() && $this->accessAdmin)
            || ($auth->isSeller() && $this->accessSeller)
            || ($auth->isBuyer() && $this->accessBuyer)
            || ($auth->isGuest() && $this->accessGuest)
        );
        
        return $result;
    }
    
}
