<?php

declare(strict_types=1);

namespace B2bShop\Controller;

use B2bShop\Module\Auth\Auth;
use B2bShop\Module\Request\Request;
use B2bShop\Module\Response\Response;
use B2bShop\Module\View\View;

/**
 * Executes actions and renders page.
 */
abstract class ControllerAbstract {
    
    /**
     * Field types.
     */
    public const CONTROL_INPUT = 'input';
    public const CONTROL_PASSWORD = 'password';
    public const CONTROL_TEXTAREA = 'textarea';
    public const CONTROL_HTML = 'html';
    public const CONTROL_SELECT_BOOL = 'selectBool';
    public const CONTROL_SELECT = 'select';
    public const CONTROL_NONE = 'none';
    
    /**
     * Initializes controller.
     */
    abstract public function init(Request $request, Response $response): void;
    
    /**
     * Execute controller action.
     */
    abstract public function execute(string $action): void;
    
    /**
     * Redirects UA to url and stops script execution.
     */
    abstract protected function redirect(string $url = null): void;
    
    /**
     * Sends 404 response to UA.
     */
    abstract protected function send404(): void;
    
    /**
     * Executes before controller action.
     */
    abstract protected function before(): void;
    
    /**
     * Executes after controller action.
     */
    abstract protected function after(): void;
    
    /**
     * Renders and prints page.
     */
    abstract protected function printPage(): void;
    
    /**
     * Gets request.
     */
    abstract protected function getRequest(): ?Request;
    
    /**
     * Sets request.
     */
    abstract protected function setRequest(Request $request);
    
    /**
     * Gets response.
     */
    abstract protected function getResponse(): ?Response;
    
    /**
     * Sets response.
     */
    abstract protected function setResponse(Response $response);    
    
    /**
     * Sets data for key. Can be gotten later.
     */
    abstract protected function setStashData(string $key, $data): bool;
    
    /**
     * Gets data for key and removes it from stash.
     */
    abstract protected function popStashData(string $key);
    
    /**
     * Gets page url.
     */
    abstract protected function getUrl(): string;
    
    /**
     * Gets site root url.
     */
    abstract protected function getRootUrl(): string;
    
    /**
     * Gets url to controller's root page.
     */
    abstract public function getBaseUrl(): string;
    
    /**
     * Gets variable value from $_GET.
     */
    abstract protected function getFromGet(string $name, string $defaultValue = null): ?string;
    
    /**
     * Gets variable value from $_POST.
     */
    abstract protected function getFromPost(string $name, string $defaultValue = null): ?string;
    
    /**
     * Gets variable value from $_SESSION.
     */
    abstract protected function getFromSession(string $name, string $defaultValue = null): ?string;
    
    /**
     * Sets variable value to $_SESSION.
     */
    abstract protected function setToSession(string $name, string $value): bool;
    
    /**
     * Unsets variable value from $_SESSION.
     */
    abstract protected function unsetFromSession(string $name): bool;
    
}
