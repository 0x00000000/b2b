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
    protected $_innerUrl = '/register';
    
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
            $this->getView()->set('messageTypeRegister', null);
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
        
        $messageTypeRegister = null;
        if (empty($user->login)) {
            $messageTypeRegister = 'emptyLogin';
        } else if (empty($this->getFromPost('password'))) {
            $messageTypeRegister = 'emptyPassword';
        } else if (empty($password)) {
            $messageTypeRegister = 'emptyPassword';
        } else if (empty($confirmPassword)) {
            $messageTypeRegister = 'emptyConfirm';
        } else if ($password !== $confirmPassword) {
            $messageTypeRegister = 'passwordDifferent';
        } else if (empty($user->organization)) {
            $messageTypeRegister = 'emptyOrganization';
        } else if (empty($user->phone)) {
            $messageTypeRegister = 'emptyPhone';
        } else if (empty($user->city)) {
            $messageTypeRegister = 'emptyCity';
        }
        
        if (empty($messageTypeRegister)) {
            if ($this->checkBot()) {
                $user->setPassword($password);
                $user->isBuyer = true;
                $user->isNew = true;
                $user->disabled = true;
                
                if (! $user->isLoginExisted($user->login)) {
                    if ($user->save()) {
                        $this->setStashData('messageType', 'successfullyRegistered');
                        $this->redirect($this->getBaseUrl());
                    } else {
                        $messageTypeRegister = 'savingFailed';
                    }
                } else {
                    $messageTypeRegister = 'existingLogin';
                }
            } else {
                $messageTypeRegister = 'checkBot';
            }
        }
        
        $this->getView()->set('messageTypeRegister', $messageTypeRegister);
        
    }
    
    protected function checkBot() {
        $questionId = $this->getFromPost('questionId');
        $questionAnswer = $this->getFromPost('questionAnswer');
        $checked = Factory::instance()->createModule('BotChecker')->checkAnswer($questionId, $questionAnswer);
        return $checked;
    }
}
