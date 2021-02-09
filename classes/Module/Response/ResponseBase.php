<?php

declare(strict_types=1);

namespace B2bShop\Module\Response;

/**
 * Constructs and sends response to UA.
 */
abstract class ResponseBase extends Response {
    
    /**
     * Response body.
     */
    protected $_body = '';
    
    /**
     * Response headers list.
     */
    protected $_headers = array();
    
    /**
     * Default 404 response body.
     */
    protected $_default404Body = '<html><body><h1>404 Page not found</h1></body></html>';
    /**
     * Gets response body.
     */
    public function getBody(): string {
        return $this->_body;
    }
    
    /**
     * Sets response body.
     */
    public function setBody(string $body) {
        $this->_body = $body;
    }
    
    /**
     * Gets response header.
     */
    public function getHeader(string $key): ?string {
        $value = null;
        
        if (array_key_exists($key, $this->_headers) !== false) {
            $value = $this->_headers[$key];
        }
        
        return $value;
    }
    
    /**
     * Sets response header.
     */
    public function setHeader(string $key, string $value = null): void {
        $this->_headers[$key] = $value;
    }
    
    /**
     * Sends response to UA.
     */
    public function send(): void {
        foreach ($this->_headers as $key => $value) {
            if ($value !== null) {
                $header = $key . ': ' . $value;
            } else {
                $header = $key;
            }
            header($header);
        }
        
        echo $this->_body;
    }
    
    /**
     * Sends 404 response to UA.
     */
    public function send404(string $body = null): void {
        if (is_null($body)) {
            $body = $this->_default404Body;
        }
        
        $this->setHeader('HTTP/1.0 404 Not Found');
        $this->setBody($body);
        $this->send();
        exit;
    }
    
}
