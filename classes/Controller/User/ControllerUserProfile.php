<?php

declare(strict_types=1);

namespace B2bShop\Controller\User;

use B2bShop\Controller\ControllerBase;

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
        $this->getView()->setTemplate('User/Profile/view');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionEdit() {
        if (array_key_exists('submit', $this->getRequest()->post)) {
            $this->innerActionDoEdit();
        }
        
        $this->getView()->setTemplate('User/Profile/edit');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoEdit() {
        $user = $this->getAuth()->getUser();
        
        $redirectUrl = $this->getUrl();
        if ($user) {
            $name = $this->getFromPost('name');
            if (strlen($name)) {
                $user->name = $name;
                
                if ($user->save()) {
                    $redirectUrl = $this->getBaseUrl();
                    $this->setStashData('messageType', 'profileUpdated');
                } else {
                    $this->setStashData('messageType', 'savingFailed');
                }
            } else {
                $this->setStashData('messageType', 'emptyName');
            }
        } else {
            $redirectUrl = $this->getAuthUrl();
        }
        
        $this->redirect($redirectUrl);
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
    
}
