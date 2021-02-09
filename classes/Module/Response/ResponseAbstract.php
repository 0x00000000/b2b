<?php

declare(strict_types=1);

namespace B2bShop\Module\Response;

/**
 * Constructs and sends response to UA.
 */
abstract class ResponseAbstract {
    
    /**
     * Gets response body.
     */
    abstract public function getBody(): string;
    
    /**
     * Sets response body.
     */
    abstract public function setBody(string $body);
    
    /**
     * Gets response header.
     */
    abstract public function getHeader(string $key): ?string;
    
    /**
     * Sets response header.
     */
    abstract public function setHeader(string $key, string $value = null): void;
    
    /**
     * Sends response to UA.
     */
    abstract public function send(): void;
    
    /**
     * Sends 404 response to UA.
     */
    abstract public function send404(): void;
    
}