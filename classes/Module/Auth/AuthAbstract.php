<?php

declare(strict_types=1);

namespace B2bShop\Module\Auth;

use B2bShop\Module\Request\Request;

use B2bShop\Model\ModelUser;

/**
 * Allows to get authorized user information.
 */
abstract class AuthAbstract {
    
    /**
     * Gets current user.
     */
    abstract public function getUser(): ?ModelUser;
    
    /**
     * Check user by user auth information.
     */
    abstract public function check($login, $password): bool;
    
    /**
     * Login user by user auth information.
     */
    abstract public function login(string $login, string $password): bool;
    
    /**
     * Logout current user by user auth information.
     */
    abstract public function logout(): bool;
    
    /**
     * Checks if user is inactive.
     */
    abstract public function isInactive(string $login, string $password): bool;
    
    /**
     * Check if user is admin.
     */
    abstract public function isAdmin();
    
    /**
     * Check if user is a seller.
     */
    abstract public function isSeller();
    
    /**
     * Check if user is a buyer.
     */
    abstract public function isBuyer();
    
    /**
     * Check if user is guest.
     */
    abstract public function isGuest();
    
    /**
     * Gets request object.
     */
    abstract protected function getRequest(): Request;
    
    /**
     * Sets request object.
     */
    abstract public function setRequest(Request $request): bool;
    
}