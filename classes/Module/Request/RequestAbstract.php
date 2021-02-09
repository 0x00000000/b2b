<?php

declare(strict_types=1);

namespace B2bShop\Module\Request;

use B2bShop\Model\ModelRequest;

/**
 * Allows to get current request information.
 * 
 * @property string|null $url Request's url (readonly).
 * @property array|null $get Request's get data (readonly).
 * @property array|null $post Request's post data (readonly).
 * @property array|null $files Request's files data (readonly).
 * @property array|null $session Request's session data (readonly).
 * @property array|null $headers Request's headers (readonly).
 * @property string|null $ip User ip (readonly).
 * @property string|null $userAgent Rser agent infoimation (readonly).
 */
abstract class RequestAbstract {
    
    /**
     * Gets current request.
     */
    abstract public function getCurrentRequest(): ModelRequest;
    
    /**
     * Sets variable to session by key.
     */
    abstract public function setSessionVariable(string $key, $value): bool;
    
    /**
     * Unsets variable from session by key.
     */
    abstract public function unsetSessionVariable(string $key): bool;
    
    /**
     * Gets site's root url.
     */
    abstract public function getRootUrl(): string;
    
}