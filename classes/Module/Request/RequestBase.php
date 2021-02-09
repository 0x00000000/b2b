<?php

declare(strict_types=1);

namespace B2bShop\Module\Request;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

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
abstract class RequestBase extends Request {
    
    /**
     * Current request.
     */
    protected $_currentRequest = null;
    
    /**
     * Class constructor.
     */
    public function __construct() {
        $this->create();
    }
    
    /**
     * Gets current request.
     */
    public function getCurrentRequest(): ModelRequest {
        return $this->_currentRequest;
    }
    
    /**
     * Some properties may be get by this magic meghod.
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
    public function __get(string $name) {
        $result = null;
        
        switch ($name) {
            case 'url':
                $result = $this->getCurrentRequest()->url;
                break;
            case 'get':
                $result = $this->getCurrentRequest()->get;
                break;
            case 'post':
                $result = $this->getCurrentRequest()->post;
                break;
            case 'files':
                $result = $this->getCurrentRequest()->files;
                break;
            case 'session':
                $result = $this->getCurrentRequest()->session;
                break;
            case 'headers':
                $result = $this->getCurrentRequest()->headers;
                break;
            case 'ip':
                $result = $this->getCurrentRequest()->ip;
                break;
            case 'userAgent':
                $result = $this->getCurrentRequest()->userAgent;
                break;
            default:
                $result = null;
                break;
        }
        
        return $result;
    }
    
    /**
     * Creates current reqeust. 
     */
    protected function create() {
        $this->_currentRequest = Factory::instance()->createModel('Request');
        
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $this->_currentRequest->url = $_SERVER['REQUEST_URI'];
        }
        $this->_currentRequest->get = isset($_GET) ? $_GET : array();
        $this->_currentRequest->post = isset($_POST) ? $_POST : array();
        $this->_currentRequest->files = isset($_FILES) ? $_FILES : array();
        $this->_currentRequest->session = isset($_SESSION) ? $_SESSION : array();
        $this->_currentRequest->headers = $this->getHeadersInner();
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $this->_currentRequest->ip = $_SERVER['REMOTE_ADDR'];
        }
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $this->_currentRequest->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        
        $this->_currentRequest->save();
    }
    
    /**
     * Sets variable to session by key.
     */
    public function setSessionVariable(string $key, $value): bool {
        $result = false;
        
        if ($key) {
            $_SESSION[$key] = $value;
            $this->_currentRequest->session = $_SESSION;
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Unsets variable from session by key.
     */
    public function unsetSessionVariable(string $key): bool {
        $result = false;
        
        if ($key) {
            unset($_SESSION[$key]);
            $this->_currentRequest->session = $_SESSION;
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Gets page's full url.
     * f. e. http://example.com/site_root/site_page1?arg=3
     */
    public function getUrl(): string {
        $url = $this->getHostUrl() . $this->getCurrentRequest()->url;
        
        return $url;
    }
    
    /**
     * Gets server's host url.
     * f. e. http://example.com
     */
    protected function getHostUrl(): string {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        
        return $url;
    }
    
    /**
     * Gets site's root url.
     * f. e. http://example.com/site_root
     */
    public function getRootUrl(): string {
        if (Config::instance()->get('application', 'baseUrl')) {
            $url = Config::instance()->get('application', 'baseUrl');
        } else {
            $url = $this->getHostUrl();
        }
        
        return $url;
    }
    
    protected function getHeadersInner(): array {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
           $headers = array();
           foreach ($_SERVER as $name => $value) {
               if (substr($name, 0, 5) == 'HTTP_') {
                   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
               }
           }
       }
       return $headers;
    }
    
}
