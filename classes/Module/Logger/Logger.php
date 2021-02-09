<?php

declare(strict_types=1);

namespace B2bShop\Module\Logger;

use B2bShop\Module\Factory\Factory;
use B2bShop\Module\Request\Request;

/**
 * Log errors and notices. Set handlers for php errors and uncaught exceptions.
 */
class Logger {
    
    /**
     * @var Request|null $_request Request object.
     */
    protected $_request = null;
    
    /**
     * Class constructor.
     */
    public function __construct() {
        
    }
    
    /**
     * Logs critical error.
     */
    public function logCritical(
        string $message, string $description = null,
        array $data = null, int $code = null,
        string $file = null, int $line = null
    ): bool {
        return self::logExtended('createCritical', $message, $description, $data, $code, $file, $line);
    }
    
    /**
     * Logs error.
     */
    public function logError(
        string $message, string $description = null,
        array $data = null, int $code = null,
        string $file = null, int $line = null
    ): bool {
        return self::logExtended('createError', $message, $description, $data, $code, $file, $line);
    }
    
    /**
     * Logs warning.
     */
    public function logWarning(
        string $message, string $description = null,
        array $data = null, int $code = null,
        string $file = null, int $line = null
    ): bool {
        return self::logExtended('createWarning', $message, $description, $data, $code, $file, $line);
    }
    
    /**
     * Logs notice.
     */
    public function logNotice(
        string $message, string $description = null,
        array $data = null, int $code = null,
        string $file = null, int $line = null
    ): bool {
        return self::logExtended('createNotice', $message, $description, $data, $code, $file, $line);
    }
    
    /**
     * Set handlers for php errors and uncaught exceptions.
     */
    public function startErrorsLogging(): void {
        set_error_handler(function($code, $message, $file, $line, $context) {
            var_export(array($code, $message, $file, $line));
            $this->logError('Error catched', $message, $context, $code, $file, $line);
        });
        
        set_exception_handler(function($exception) {
            var_export($exception);
            $this->logError(
                'Exception catched', $exception->getMessage(),
                $exception->getTrace(), $exception->getCode(),
                $exception->getFile(), $exception->getLine()
            );
        });
    }
    
    /**
     * Save log.
     */
    protected function logExtended(
        string $methodName, string $message, string $description = null,
        array $data = null, int $code = null,
        string $file = null, int $line = null
    ): bool {
        $result = false;
        $modelLog = Factory::instance()->createModel('Log');
        $modelLog->setRequest($this->getRequest());
        
        if (method_exists($modelLog, $methodName)) {
            if ($this->getRequest()) {
                $url = $this->getRequest()->url;
            } else {
                $url = null;
            }
            
            $result = $modelLog->$methodName($message, $description, $data, $code, $file, $line, $url);
            if ($result) {
                $modelLog->save();
            }
        }
        
        return $result;
    }
    
    /**
     * Gets request object.
     */
    protected function getRequest(): Request {
        return $this->_request;
    }
    
    /**
     * Sets request object.
     */
    public function setRequest(Request $request): bool {
        $result = false;
        
        if (is_object($request) && $request instanceof Request) {
            $this->_request = $request;
            $result = true;
        }
        
        return $result;
    }
    
}
