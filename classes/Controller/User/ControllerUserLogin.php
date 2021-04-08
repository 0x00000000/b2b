<?php

declare(strict_types=1);

namespace B2bShop\Controller\User;

use B2bShop\Module\Config\Config;
use B2bShop\Controller\ControllerBase;

class ControllerUserLogin extends ControllerBase {
    
    protected function actionIndex() {
        
        if ($this->getAuth()->isGuest()) {
            $content = $this->innerActionLogin();
        } else {
            $content = $this->innerActionLogout();
        }
        
        return $content;
    }
    
    protected function actionLogout() {
        
        if ($this->getAuth()->isGuest()) {
            $this->redirect($this->getAuthUrl());
        } else {
            $this->innerActionDoLogout();
        }
    }
    
    protected function innerActionLogin() {
        
        if (array_key_exists('login', $this->getRequest()->post)) {
            $this->innerActionDoLogin();
        }
        
        $this->getView()->setTemplate('User/login');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoLogin() {
        sleep(1);
        if ($this->getFromPost('login') && $this->getFromPost('password')) {
            sleep(1);
            if ($this->getAuth()->login($this->getFromPost('login'), $this->getFromPost('password'))) {
                $this->setStashData('messageType', '');
                $redirectAfterLoginUrl = $this->popStashData('redirectAfterLoginUrl');
                $redirectAfterLoginTime = $this->popStashData('redirectAfterLoginTime');
                if ($redirectAfterLoginUrl && $redirectAfterLoginTime && $redirectAfterLoginTime + Config::instance()->get('site', 'redirectAfterLoginLifeTime') > time()) {
                    $this->redirect($redirectAfterLoginUrl);
                } else if ($this->getAuth()->isAdmin()) {
                    $this->redirect($this->getRootUrl() . Config::instance()->get('admin', 'mainPageUrl'));
                } else if ($this->getAuth()->isSeller()) {
                    $this->redirect($this->getRootUrl() . Config::instance()->get('seller', 'mainPageUrl'));
                } else if ($this->getAuth()->isBuyer()) {
                    $this->redirect($this->getRootUrl() . Config::instance()->get('buyer', 'mainPageUrl'));
                } else {
                    $this->redirect($this->getRootUrl());
                }
            } else if ($this->getAuth()->isInactive($this->getFromPost('login'), $this->getFromPost('password'))) {
                $this->setStashData('messageType', 'loginInactive');
                $this->redirect();
            } else {
                $this->setStashData('messageType', 'loginFailed');
                $this->redirect();
            }
        }
    }
    
    protected function innerActionLogout() {
        if (array_key_exists('logout', $this->getRequest()->post)) {
            $this->innerActionDoLogout();
        }
        
        $this->getView()->setTemplate('User/logout');
        
        $this->getView()->set('user', $this->getAuth()->getUser());
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoLogout() {
        if ($this->getAuth()->logout()) {
            $this->redirect($this->getRootUrl());
        }
    }
    
}
