<?php

declare(strict_types=1);

namespace B2bShop\Controller;

use B2bShop\Module\Factory\Factory;

use B2bShop\Model\Model;
use B2bShop\Model\ModelDatabase;

/**
 * Renders pages for managing database model items.
 */
abstract class ControllerManageBase extends ControllerBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = '';
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('deleted' => false,);
    
    /**
     * @var array $_sortingList Default sorting list.
     */
    protected $_sortingList = array('id' => 'desc',);
    
    /**
     * @var array $_itemsPerPage Items per page.
     */
    protected $_itemsPerPage = 20;
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'add' => 'Common/ManageBase/add',
        'list' => 'Common/ManageBase/list',
        'view' => 'Common/ManageBase/view',
        'edit' => 'Common/ManageBase/edit',
    );
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array();
    
    /**
     * @var string $_action Action paramether.
     */
    protected $_action = null;
    
    /**
     * @var string $_id Id paramether.
     */
    protected $_id = null;
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        if (array_key_exists('action', $get)) {
            $this->_action = $get['action'];
        }
        if (array_key_exists('id', $get)) {
            $this->_id = $get['id'];
        }
        
        if ($this->_action === 'add') {
            $content = $this->innerActionAdd();
        } else if ($this->_action === 'view' && $this->_id) {
            $content = $this->innerActionView();
        } else if ($this->_action === 'edit' && $this->_id) {
            $content = $this->innerActionEdit();
        } else if ($this->_action === 'delete' && $this->_id) {
            $content = $this->innerActionDelete();
        } else if ($this->_action === 'disable' && $this->_id) {
            $content = $this->innerActionDisable(); // Disables or enables item.
        } else if ($this->_action === 'list') {
            $content = $this->innerActionList();
        } else if (empty($this->_action)) {
            $content = $this->innerActionList();
        } else {
            $content = '';
            $this->send404();
        }
        
        return $content;
    }
    
    protected function setContentViewVariables() {
        parent::setContentViewVariables();
        
        $model = Factory::instance()->createModel($this->_modelName);
        $propertiesList = $model->getPropertiesList();
        $this->getView()->set('propertiesList', $propertiesList);
        $controlsList = $this->getModelControlsList();
        $this->getView()->set('controlsList', $controlsList);
        
        $propertiesList = $model->getPropertiesList();
        foreach ($propertiesList as $propertyName => $propertyData) {
            if (
                $propertyData['type'] === Model::TYPE_ENUM
                && array_key_exists('getValuesMethodName', $propertyData)
                && method_exists($model, $propertyData['getValuesMethodName'])
            ) {
                $methodName = $propertyData['getValuesMethodName'];
                $values = $model->$methodName();
                $this->getView()->set($propertyData['name'] . 'Values', $values);
            }
        }
    }
    
    protected function setPropertiesFromPost(ModelDatabase $model): bool {
        $canSave = false;
        $propertiesList = $model->getPropertiesList();
        $controlsList = $this->getModelControlsList();
        
        foreach ($propertiesList as $propertyName => $propertyData) {
            if (! $model->isPk($propertyName)) {
                if ($controlsList[$propertyName] !== self::CONTROL_NONE) {
                    $value = $this->getFromPost($propertyName);
                    if ($controlsList[$propertyName] !== self::CONTROL_PASSWORD || $value !== '') {
                        $value = $this->convertFromPost($value, $propertyData);
                        if (! is_null($value)) {
                            $model->$propertyName = $value;
                            $canSave = true;
                        }
                    }
                }
            }
        }
        
        return $canSave;
    }
    
    /**
     * Gets values list for foreign key field.
     * 
     * @param  string $fkPropertyName Foreign key property name.
     * @param  string $condition Condition for selecting values for field.
     * @param  string $fkModelCaptionField Name of linked model field.
     * @return array
     */
    protected function getFkValues(string $fkPropertyName, array $condition, string $fkModelCaptionField = 'name', $addEmptyValue = false): array {
        if ($addEmptyValue) {
            $itemsList = array('' => ' ');
        } else {
            $itemsList = array();
        }
        
        $model = Factory::instance()->createModel($this->_modelName);
        $propertiesList = $model->getPropertiesList();
        
        $defaultCondition = array('deleted' => false, 'disabled' => false);
        $fkModelName = $propertiesList[$fkPropertyName]['fkModelName'];
        $fkModel = Factory::instance()->createModel($fkModelName);
        $fkModelsList = $fkModel->getModelsList(
            array_merge($condition, $defaultCondition)
        );
        if ($fkModelsList) {
            foreach ($fkModelsList as $fkModelData) {
                $itemsList[$fkModelData->id] = $fkModelData->$fkModelCaptionField;
            }
        }
        
        return $itemsList;
    }
    
    protected function innerActionAdd() {
        if ($this->getFromPost('submit')) {
            $this->innerActionDoAdd();
        }
        
        if (array_key_exists('add', $this->_templateNames)) {
            $this->getView()->setTemplate($this->_templateNames['add']);
        }
        
        $model = Factory::instance()->createModel($this->_modelName);
        $this->getView()->set('model', $model);
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoAdd() {
        $model = Factory::instance()->createModel($this->_modelName);
        $canSave = $this->setPropertiesFromPost($model);
        if ($canSave) {
            if ($model->save()) {
                $this->setStashData('messageType', 'addedSuccessfully');
            } else {
                var_export($model->getLastError());
                exit;
                $this->setStashData('messageType', 'addingFailed');
            }
        } else {
            $this->setStashData('messageType', 'addingFailed');
        }
        $this->redirect($this->getBaseUrl());
        
    }
    
    protected function innerActionView() {
        if (array_key_exists('view', $this->_templateNames)) {
            $this->getView()->setTemplate($this->_templateNames['view']);
        }
        
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $item = null;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $this->getView()->set('item', $item);
            $content = $this->getView()->render();
        } else {
            $this->setStashData('messageType', 'itemNotFound');
            $this->redirect($this->getBaseUrl());
        }
        
        return $content;
    }
    
    protected function innerActionEdit() {
        if ($this->getFromPost('submit')) {
            $this->innerActionDoEdit();
        }
        if (array_key_exists('edit', $this->_templateNames)) {
            $this->getView()->setTemplate($this->_templateNames['edit']);
        }
        
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $this->getView()->set('item', $item);
            $content = $this->getView()->render();
        } else {
            $this->setStashData('messageType', 'itemNotFound');
            $this->redirect($this->getBaseUrl());
        }
        
        return $content;
    }
    
    protected function innerActionDoEdit() {
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $canSave = $this->setPropertiesFromPost($item);
            
            if ($canSave) {
                if ($item->save()) {
                    $this->setStashData('messageType', 'editedSuccessfully');
                } else {
                    $this->setStashData('messageType', 'editingFailed');
                }
            } else {
                $this->setStashData('messageType', 'editingFailed');
            }
            
        } else {
            $this->setStashData('messageType', 'itemNotFound');
        }
        $this->redirect($this->getBaseUrl());
        
    }
    
    protected function innerActionDelete() {
        $this->innerActionDoDelete();
        
        return '';
    }
    
    protected function innerActionDoDelete() {
        if ($this->_id) {
            $conditionsList = $this->_conditionsList;
            $conditionsList['id'] = $this->_id;
            $model = Factory::instance()->createModel($this->_modelName);
            $item = $model->getOneModel($conditionsList);
            
            if ($item) {
                $item->deleted = true;
                if ($item->save()) {
                    $this->setStashData('messageType', 'deletedSuccessfully');
                } else {
                    $this->setStashData('messageType', 'deletingFailed');
                }
            } else {
                $this->setStashData('messageType', 'itemNotFound');
            }
        } else {
            $this->setStashData('messageType', 'wrongParamethers');
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function innerActionDisable() {
        $this->innerActionDoDisable();
        
        return '';
    }
    
    protected function innerActionDoDisable() {
        if ($this->_id) {
            $conditionsList = $this->_conditionsList;
            $conditionsList['id'] = $this->_id;
            $model = Factory::instance()->createModel($this->_modelName);
            $item = $model->getOneModel($conditionsList);
            
            if ($item) {
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
    
    protected function innerActionList() {
        if (array_key_exists('list', $this->_templateNames)) {
            $this->getView()->setTemplate($this->_templateNames['list']);
        }
        
        if ($this->_id) {
            $currentPage = (int) $this->_id;
            if ($currentPage <= 0) {
                $currentPage = 1;
            }
        } else {
            $currentPage = 1;
        }
        
        $itemsList = array();
        $model = Factory::instance()->createModel($this->_modelName);
        $itemsList = $model->getModelsList(
            $this->_conditionsList,
            $this->_itemsPerPage,
            ((int) $currentPage - 1) * (int) $this->_itemsPerPage,
            $this->_sortingList
            
        );
        
        $modelsCount = $model->getCount(
            $this->_conditionsList
        );
        
        $pagesList = array();
        if ($modelsCount > 1) {
            $pagesCount = floor(($modelsCount - 1) / $this->_itemsPerPage);
            for ($i = 1; $i <= $pagesCount; $i++) {
                $pagesList[] = (string) $i;
            }
        } else {
            $pagesCount = 1;
        }
        
        $this->getView()->set('itemsList', $itemsList);
        $this->getView()->set('currentPage', $currentPage);
        $this->getView()->set('pagesCount', $pagesCount);
        $this->getView()->set('pagesList', $pagesList);
        
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function convertFromPost(?string $value, array $propertyData = array()) {
        if (array_key_exists('type', $propertyData) && $propertyData['type'] === ModelDatabase::TYPE_BOOL) {
            if ($value === '') {
                $preparedValue = null;
            } else if ($value === '1') {
                $preparedValue = true;
            } else {
                $preparedValue = false;
            }
        } else {
            $preparedValue = $value;
        }
        
        return $preparedValue;
    }
    
    protected function getModelControlsList() {
        $model = Factory::instance()->createModel($this->_modelName);
        $propertiesList = $model->getPropertiesList();
        $this->getView()->set('propertiesList', $propertiesList);
        
        $controlsList = $this->_modelControlsList;
        
        foreach ($propertiesList as $propertyName => $property) {
            if (! array_key_exists($propertyName, $controlsList)) {
                if (! empty($property['skipControl'])) {
                    $controlType = self::CONTROL_NONE;
                } else {
                    switch ($property['type']) {
                        case Model::TYPE_TEXT:
                        case Model::TYPE_INT:
                            $controlType = self::CONTROL_INPUT;
                            break;
                        case Model::TYPE_BOOL:
                            $controlType = self::CONTROL_SELECT_BOOL;
                            break;
                        case Model::TYPE_FK:
                            $controlType = self::CONTROL_SELECT;
                            break;
                        case Model::TYPE_ENUM:
                            $controlType = self::CONTROL_SELECT;
                            break;
                        default:
                            $controlType = self::CONTROL_INPUT;
                            break;
                    }
                }
                $controlsList[$propertyName] = $controlType;
            }
        }
        return $controlsList;
    }
    
}
