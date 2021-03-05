<?php

declare(strict_types=1);

namespace B2bShop\Controller\User;

use B2bShop\Module\Factory\Factory;

use B2bShop\Controller\ControllerBase;

use B2bShop\Model\ModelDatabase;

class ControllerUserRegister extends ControllerBase {
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/profile';
    
    /**
     * Executes before controller action.
     */
    protected function before(): void {
        if (! $this->getAuth()->isGuest()) {
            $this->redirect($this->getProfileUrl());
        }
    }
    
    protected function actionRegister() {
        $user = Factory::instance()->createModel('Buyer');
        
        if ($this->getFromPost('submit')) {
            $this->innerActionDoRegister();
            
            $this->setPropertiesFromPost($user);
        } else {
            $this->getView()->set('messageType', null);
        }
        
        $this->getView()->set('user', $user);
        
        $question = Factory::instance()->createModule('BotChecker')->getRandomQuestion();
        
        $this->getView()->set('question', $question);
        
        $this->getView()->setTemplate('User/register');
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoRegister() {
        $user = Factory::instance()->createModel('Buyer');
        
        $this->setPropertiesFromPost($user);
        
        $password = $this->getFromPost('password');
        $confirmPassword = $this->getFromPost('confirmPassword');
        
        $messageType = null;
        if (empty($user->login)) {
            $messageType = 'emptyLogin';
        } else if (empty($this->getFromPost('password'))) {
            $messageType = 'emptyPassword';
        } else if (empty($password)) {
            $messageType = 'emptyPassword';
        } else if (empty($confirmPassword)) {
            $messageType = 'emptyConfirm';
        } else if ($password !== $confirmPassword) {
            $messageType = 'passwordDifferent';
        } else if (empty($user->organization)) {
            $messageType = 'emptyOrganization';
        } else if (empty($user->phone)) {
            $messageType = 'emptyPhone';
        } else if (empty($user->city)) {
            $messageType = 'emptyCity';
        }
        
        if (empty($messageType)) {
            if ($this->checkBot()) {
                $user->setPassword($password);
                $user->isBuyer = true;
                $user->isNew = true;
                $user->disabled = true;
                
                if (! $user->isLoginExisted($user->login)) {
                    if ($user->save()) {
                        $this->setStashData('messageType', 'successfullyRegistered');
                        $this->redirect($this->getAuthUrl());
                    } else {
                        $messageType = 'savingFailed';
                    }
                } else {
                    $messageType = 'existingLogin';
                }
            } else {
                $messageType = 'checkBot';
            }
        }
        
        $this->getView()->set('messageType', $messageType);
        
    }
    
    protected function checkBot() {
        $questionId = $this->getFromPost('questionId');
        $questionAnswer = $this->getFromPost('questionAnswer');
        $checked = Factory::instance()->createModule('BotChecker')->checkAnswer($questionId, $questionAnswer);
        return $checked;
    }
}
