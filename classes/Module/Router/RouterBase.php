<?php

declare(strict_types=1);

namespace B2bShop\Module\Router;

use B2bShop\System\Core;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;
use B2bShop\Module\Request\Request;
use B2bShop\Module\Response\Response;

use B2bShop\Controller\Controller;

/**
 * Routes UA.
 */
abstract class RouterBase extends Router {
    
    /**
     * Routing rules list.
     */
    protected $_rulesList = array();
    
    /**
     * Default routing rule list.
     */
    protected $_defaultRule = array();
    
    /**
     * Request object.
     */
    protected $_request = null;
    
    /**
     * Response object.
     */
    protected $_response = null;
    
    /**
     * Routes UA.
     */
    public function route(): void {
        $found = false;
        if ($this->getRequest() && $this->getResponse()) {
            if ($this->getResponse() && $this->getResponse()) {
                foreach ($this->_rulesList as $rule) {
                    $getData = array();
                    if ($this->checkRule($rule, $getData)) {
                        $this->addGetData($getData);
                        $found = $this->apply($rule);
                        break;
                    }
                }
            }
            
            if (! $found) {
                $found = $this->apply($this->_defaultRule);
            }
            
            if (! $found) {
                $this->send404();
            }
            
        } else {
            $message = '';
            if (! $this->getRequest()) {
                $message .= 'Request is not set. ';
            }
            if (! $this->getResponse()) {
                $message .= 'Response is not set. ';
            }
            
            Core::FatalError($message);
        }
        
    }
    
    /**
     * Initializes router.
     */
    public function init(Request $request, Response $response): void {
        $this->_request = $request;
        $this->_response = $response;
    }
    
    /**
     * Adds route rule.
     */
    public function setRule(string $rule, string $controller, string $action): bool {
        $result = false;
        
        if (strlen($rule) && strlen($controller) && strlen($action)) {
            $this->_rulesList[] = array(
                'rule' => $rule,
                'controller' => $controller,
                'action' => $action,
            );
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Sets default route rule.
     */
    public function setDefaultRule(string $controller, string $action): bool {
        $result = false;
        
        if (strlen($controller) && strlen($action)) {
            $this->_defaultRule = array(
                'rule' => '',
                'controller' => $controller,
                'action' => $action,
            );
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Gets request.
     */
    protected function getRequest(): Request {
        return $this->_request;
    }
    
    /**
     * Gets response.
     */
    protected function getResponse(): Response {
        return $this->_response;
    }
    
    /**
     * Checks if rule fits to request.
     */
    public function checkRule(array $ruleData, array &$getData): bool {
        $result = false;
        
        $rule = $ruleData['rule'];
        
        $result = $this->checkRuleOptional($rule, $getData);
        
        return $result;
    }
    
    /**
     * Checks if rule fits to request. Checks optional pieces.
     */
    public function checkRuleOptional(string $rule, array &$getData): bool {
        $result = false;
        
        $ruleCut = $rule;
        
        $ruleReplaced = str_replace(array('[', ']'), '', $ruleCut);
        $result = $this->checkRuleParameterized($ruleReplaced, $getData);
        
        $pattern = '|^(.*)(\[[^]]+\])(.*)$|';
        while (
            ! $result
            && preg_match($pattern, $ruleCut)
        ) {
            $ruleCut = preg_replace($pattern, '$1$3', $ruleCut);
            $ruleReplaced = str_replace(array('[', ']'), '', $ruleCut);
            $result = $this->checkRuleParameterized($ruleReplaced, $getData);
        }
        
        return $result;
    }
    
    /**
     * Checks if rule fits to request.
     * 
     * $rule should not contain "[" or "]".
     */
    public function checkRuleParameterized(string $rule, array &$getData): bool {
        $result = false;
        
        $url = $this->getRequest()->url;
        $urlPrefix = Config::instance()->get('application', 'urlPrefix');
        if ($urlPrefix) { // If application is not in site's root.
            $url = preg_replace('|^' . $urlPrefix . '|', '', $url);
        }
        
        $pattern = '|[^<]*<([\w_]+)>[^<]*|';
        if (preg_match($pattern, $rule)) {
            $namesList = array();
            $pregRule = $rule;
            while (preg_match($pattern, $pregRule, $matchesName)) {
                $replaced = false;
                if (isset($matchesName[1]) && $matchesName[1]) {
                    $name = $matchesName[1];
                    $namesList[] = $name;
                    $replacementsCount = 1;
                    $pregRule = str_replace('<' . $name . '>', '([\w_-]+)', $pregRule, $replacementsCount);
                } else {
                    break;
                }
            }
            
            if (preg_match('|^'.$pregRule.'$|', $url, $matchesValues)) {
                $result = true;
                
                if (count($matchesValues) === count($namesList) + 1) {
                    for ($i = 0; $i < count($namesList); $i++) {
                        $getData[$namesList[$i]] = $matchesValues[$i + 1];
                    }
                }
            }
        } else {
            $result = strcmp($rule, $url) === 0;
        }
        
        return $result;
    }
    
    /**
     * Adds get paramethers.
     */
    public function addGetData(array $getData) {
        if (count($getData)) {
            $get = $this->getRequest()->getCurrentRequest()->get;
            foreach ($getData as $key => $val) {
                $get[$key] = $val;
            }
            $this->getRequest()->getCurrentRequest()->get = $get;
        }
    }
    
    /**
     * Calls controller action.
     */
    protected function apply(array $ruleData): bool {
        $result = false;
        
        if (
            array_key_exists('controller', $ruleData)
            && is_string($ruleData['controller'])
            && strlen($ruleData['controller'])
            && array_key_exists('action', $ruleData)
            && is_string($ruleData['action'])
            && strlen($ruleData['action'])
        ) {
            $controller = Factory::instance()->createController($ruleData['controller'], $this->getRequest(), $this->getResponse());
            if ($controller instanceof Controller) {
                $controller->execute($ruleData['action']);
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Sends 404 response to UA.
     */
    protected function send404(): void {
        $this->getResponse()->send404();
    }
    
}
