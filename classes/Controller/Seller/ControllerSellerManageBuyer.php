<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\Module\Factory\Factory;

use B2bShop\Model\ModelDatabase;

class ControllerSellerManageBuyer extends ControllerSellerManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'Buyer';
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('isBuyer' => true, 'isSeller' => false, 'isAdmin' => false, 'deleted' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('disabled' => 'asc', 'organization' => 'asc');
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'password' => self::CONTROL_PASSWORD,
        'status' => self::CONTROL_SELECT,
        'isAdmin' => self::CONTROL_NONE,
        'isSeller' => self::CONTROL_NONE,
        'isBuyer' => self::CONTROL_NONE,
        'isNew' => self::CONTROL_NONE,
        'deleted' => self::CONTROL_NONE,
    );
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/seller/buyer';
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'add' => 'Common/ManageBase/add',
        'list' => 'Seller/Buyer/list',
        'view' => 'Common/ManageBase/view',
        'edit' => 'Common/ManageBase/edit',
    );
    
    protected function setPropertiesFromPost(ModelDatabase $model): bool {
        $result = parent::setPropertiesFromPost($model);
        
        if ($result) {
            $model->isAdmin = false;
            $model->isSeller = false;
            $model->isBuyer = true;
        }
        
        return $result;
    }
    
    protected function innerActionDoAdd() {
        $model = Factory::instance()->createModel($this->_modelName);
        $canSave = $this->setPropertiesFromPost($model);
        $model->isNew = true;
        if ($canSave) {
            if ($model->save()) {
                $this->setStashData('messageType', 'addedSuccessfully');
            } else {
                $this->setStashData('messageType', 'addingFailed');
            }
        } else {
            $this->setStashData('messageType', 'addingFailed');
        }
        $this->redirect($this->getBaseUrl());
        
    }
    
    protected function innerActionDoDisable() {
        if ($this->_id) {
            $conditionsList = $this->_conditionsList;
            $conditionsList['id'] = $this->_id;
            $model = Factory::instance()->createModel($this->_modelName);
            $item = $model->getOneModel($conditionsList);
            
            if ($item) {
                $item->isNew = false;
                if ($item->disabled) {
                    $item->disabled = false;
                } else {
                    $item->disabled = true;
                }
                if ($item->save()) {
                    if ($item->disabled) {
                        $this->setStashData('messageType', 'disabledSuccessfully');
                    } else {
                        $this->setStashData('messageType', 'enabledSuccessfully');
                    }
                } else {
                    if ($item->disabled) {
                        $this->setStashData('messageType', 'disablingFailed');
                    } else {
                        $this->setStashData('messageType', 'enabledFailed');
                    }
                }
            } else {
                $this->setStashData('messageType', 'itemNotFound');
            }
        } else {
            $this->setStashData('messageType', 'wrongParamethers');
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function setContentViewVariables() {
        parent::setContentViewVariables();
        
        $this->getView()->set('statusValues', $this->getStatusValues());
    }
    
    protected function getStatusValues() {
        return array(
            '0' => 'Потенциальный',
            '1' => 'Работающий',
            '2' => 'Бывший',
        );
    }
    
}
