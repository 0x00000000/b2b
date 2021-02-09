<?php

declare(strict_types=1);

namespace B2bShop\Model;

/**
 * Model request.
 * Gets and saves http request's data.
 * 
 * @property string|null $id Log's id.
 * @property string|null $url Request's url.
 * @property array|null $get Request's get data.
 * @property array|null $post Request's post data.
 * @property array|null $files Request's files data.
 * @property array|null $session Request's session data.
 * @property array|null $headers Request's headers.
 * @property string|null $ip User ip.
 * @property string|null $userAgent Rser agent infoimation.
 * @property string|null $info Addititional infoimation.
 */
class ModelRequest extends ModelDatabase {
    /**
     * Default request url if $_SERVER['REQUEST_URI'] is not set.
     */
    public const UNKNOWN_REQUEST_URI = 'UNKNOWN_REQUEST_URI';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'url'),
        array('name' => 'get'),
        array('name' => 'post'),
        array('name' => 'files'),
        array('name' => 'session'),
        array('name' => 'headers'),
        array('name' => 'ip'),
        array('name' => 'userAgent'),
        array('name' => 'info'),
    );
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'request';
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Sets current http request information to this object.
     */
    public function create() {
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $this->url = $_SERVER['REQUEST_URI'];
        } else {
            $this->url = self::UNKNOWN_REQUEST_URI;
        }
        
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        if (! empty($_SESSION)) {
            $this->session = $_SESSION;
        }
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        }
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
    }
    
    /**
     * Gets request's get data.
     */
    public function getGet(): ?array {
        $get = $this->getRawProperty('get');
        if ($get) {
            $get = json_decode($get, true);
        }

        return $get;
    }
    
    /**
     * Sets request's set data.
     */
    public function setGet(?array $value): void {
        $get = json_encode($value);
        $this->setRawProperty('get', $get);
    }
    
    /**
     * Gets request's post data.
     */
    public function getPost(): ?array {
        $post = $this->getRawProperty('post');
        if ($post) {
            $post = json_decode($post, true);
        }

        return $post;
    }
    
    /**
     * Sets request's post data.
     */
    public function setPost(?array $value): void {
        $post = json_encode($value);
        $this->setRawProperty('post', $post);
    }
    
    /**
     * Gets request's files data.
     */
    public function getFiles(): ?array {
        $files = $this->getRawProperty('files');
        if ($files) {
            $files = json_decode($files, true);
        }

        return $files;
    }
    
    /**
     * Sets request's files data.
     */
    public function setFiles(?array $value): void {
        $files = json_encode($value);
        $this->setRawProperty('files', $files);
    }
    
    /**
     * Gets request's session data.
     */
    public function getSession(): ?array {
        $session = $this->getRawProperty('session');
        if ($session) {
            $session = json_decode($session, true);
        }

        return $session;
    }
    
    /**
     * Sets request's session data.
     */
    public function setSession(?array $value): void {
        $session = json_encode($value);
        $this->setRawProperty('session', $session);
    }
    
    /**
     * Gets request's headers.
     */
    public function getHeaders(): ?array {
        $headers = $this->getRawProperty('headers');
        if ($headers) {
            $headers = json_decode($headers, true);
        }

        return $headers;
    }
    
    /**
     * Sets request's headers.
     */
    public function setHeaders(?array $value): void {
        $headers = json_encode($value);
        $this->setRawProperty('headers', $headers);
    }
    
}