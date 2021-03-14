<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Module\Factory\Factory;

use B2bShop\Model\ModelDatabase;
use B2bShop\Model\ModelUser;

abstract class ControllerAdminManageUser extends ControllerAdminManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'User';
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('disabled' => 'asc', 'name' => 'asc');
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'add' => 'Admin/ManageUser/add',
        'list' => 'Common/ManageBase/list',
        'view' => 'Common/ManageBase/view',
        'edit' => 'Admin/ManageUser/edit',
    );
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'password' => self::CONTROL_PASSWORD,
        'isAdmin' => self::CONTROL_NONE,
        'isSeller' => self::CONTROL_NONE,
        'isBuyer' => self::CONTROL_NONE,
        'deleted' => self::CONTROL_NONE,
    );
    
    protected function innerActionDoAdd() {
        $url = $this->getBaseUrl();
        $model = Factory::instance()->createModel($this->_modelName);
        $canSave = $this->setPropertiesFromPost($model);
        if ($canSave) {
            if ($this->getRequest()->post['password']) {
                if ($this->checkLoginUnique($model)) {
                    if ($model->save()) {
                        $this->setStashData('messageType', 'addedSuccessfully');
                    } else {
                        $this->setStashData('messageType', 'addingFailed');
                    }
                } else {
                    $this->setStashData('messageType', 'loginExists');
                    $url = $this->getUrl();
                }
            } else {
                $this->setStashData('messageType', 'emptyPassword');
                $url = $this->getUrl();
            }
        } else {
            $this->setStashData('messageType', 'addingFailed');
        }
        $this->redirect($url);
        
    }
    
    protected function innerActionDoEdit() {
        $url = $this->getBaseUrl();
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $canSave = $this->setPropertiesFromPost($item);
            
            if ($canSave) {
                if ($this->checkLoginUnique($item)) {
                    if ($item->save()) {
                        $this->setStashData('messageType', 'editedSuccessfully');
                    } else {
                        $this->setStashData('messageType', 'editingFailed');
                    }
                } else {
                    $this->setStashData('messageType', 'loginExists');
                    $url = $this->getUrl();
                }
            } else {
                $this->setStashData('messageType', 'editingFailed');
            }
            
        } else {
            $this->setStashData('messageType', 'itemNotFound');
        }
        $this->redirect($url);
        
    }
    
    
    protected function checkLoginUnique(ModelUser $user) {
        $result = true;
        
        if (! empty($user->login)) {
            $conditionsList = array('deleted' => false, 'login' => $user->login);
            $existingUser = $user->getOneModel($conditionsList);
            if (! empty($existingUser)) {
                if (
                    empty($user->id) // For new users.
                    || (int) $user->id !== (int) $existingUser->id
                ) { 
                    $result = false;
                }
            }
        }
        
        return $result;
    }
}
