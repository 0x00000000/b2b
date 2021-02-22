<?php

declare(strict_types=1);

namespace B2bShop\Controller\User;

use B2bShop\Controller\ControllerBase;

use B2bShop\Model\ModelDatabase;

class ControllerUserProfile extends ControllerBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/profile';
    
    /**
     * Executes before controller action.
     */
    protected function before(): void {
        if ($this->getAuth()->isGuest()) {
            $this->redirect($this->getAuthUrl());
        }
    }
    
    protected function setContentViewVariables() {
        parent::setContentViewVariables();
        
        $this->getView()->set('user', $this->getAuth()->getUser());
    }
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        $action = null;
        if (array_key_exists('action', $get)) {
            $action = $get['action'];
        }
        
        if ($action === 'edit') {
            $content = $this->innerActionEdit();
        } else if ($action === 'password') {
            $content = $this->innerActionPassword();
        } else if (empty($action)) {
            $content = $this->innerActionView();
        } else {
            $content = '';
            $this->send404();
        }
        
        return $content;
    }
    
    protected function innerActionView() {
        if ($this->getAuth()->isBuyer()) {
            $this->getView()->setTemplate('Buyer/Profile/view');
        } else {
            $this->getView()->setTemplate('User/Profile/view');
        }
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionEdit() {
        $user = $this->getAuth()->getUser();
        if (array_key_exists('submit', $this->getRequest()->post)) {
            $this->innerActionDoEdit();
            
            $this->setPropertiesFromPost($user);
        } else {
            $this->getView()->set('messageType', null);
        }
        
        if ($this->getAuth()->isBuyer()) {
            $this->getView()->setTemplate('Buyer/Profile/edit');
        } else {
            $this->getView()->setTemplate('User/Profile/edit');
        }
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoEdit() {
        $user = $this->getAuth()->getUser();
        
        $redirectUrl = null;
        if ($user) {
            $this->setPropertiesFromPost($user);
            
            $messageType = null;
            if (empty($user->login)) {
                $messageType = 'emptyLogin';
            } else if (empty($user->name)) {
                $messageType = 'emptyName';
            } else if ($this->getAuth()->isBuyer()) {
                if (empty($user->organization)) {
                    $messageType = 'emptyOrganization';
                } else if (empty($user->phone)) {
                    $messageType = 'emptyPhone';
                }
            }
            
            if (empty($messageType)) {
                if ($user->save()) {
                    $redirectUrl = $this->getBaseUrl();
                    $messageType = 'profileUpdated';
                } else {
                    $messageType = 'savingFailed';
                }
            }
            
        } else {
            $redirectUrl = $this->getAuthUrl();
        }
        
        if ($redirectUrl) {
            $this->setStashData('messageType', $messageType);
            $this->redirect($redirectUrl);
        } else {
            $this->getView()->set('messageType', $messageType);
        }
    }
    
    protected function innerActionPassword() {
        if (array_key_exists('submit', $this->getRequest()->post)) {
            $this->innerActionDoPassword();
        }
        
        $this->getView()->setTemplate('User/Profile/password');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoPassword() {
        $user = $this->getAuth()->getUser();
        
        $redirectUrl = $this->getUrl();
        if ($user) {
            $password = $this->getFromPost('password');
            $confirmPassword = $this->getFromPost('confirmPassword');
            if (! $password) {
                $this->setStashData('messageType', 'emptyPassword');
            } else if (! $confirmPassword) {
                $this->setStashData('messageType', 'emptyConfirm');
            } else if ($password !== $confirmPassword) {
                $this->setStashData('messageType', 'passwordDifferent');
            } else {
                $user->setPassword($password);
                
                if ($user->save()) {
                    $redirectUrl = $this->getBaseUrl();
                    $this->setStashData('messageType', 'passwordChanged');
                } else {
                    $this->setStashData('messageType', 'savingFailed');
                }
            }
        } else {
            $redirectUrl = $this->getAuthUrl();
        }
        
        $this->redirect($redirectUrl);
    }
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     * f. e.
     * 'deleted' => self::CONTROL_NONE,
     */
    protected function getControlsList(ModelDatabase $model) {
        return array('password' => self::CONTROL_NONE,);
    }
    
}
