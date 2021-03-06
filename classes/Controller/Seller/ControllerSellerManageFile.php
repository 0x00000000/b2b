<?php

declare(strict_types=1);

namespace B2bShop\Controller\Seller;

use B2bShop\Module\Factory\Factory;

class ControllerSellerManageFile extends ControllerSellerManageBase {
    
    /**
     * @var string $_modelName Name of managed model class.
     */
    protected $_modelName = 'File';
    
    /**
     * @var string $_innerUrl Inner url to controller's root page. Should start from '/'.
     */
    protected $_innerUrl = '/seller/file';
    
    /**
     * @var string $_downloadUrl Inner url to download files controller's root page. Should start from '/'.
     */
    protected $_downloadUrl = '/file';
    
    /**
     * @var array $_modelControlsList Defines controls for model properties.
     * 
     * propertyName => controlType
     */
    protected $_modelControlsList = array(
        'type' => self::CONTROL_NONE,
        'content' => self::CONTROL_FILE,
    );
    
    protected function innerActionDoAdd() {
        $model = Factory::instance()->createModel($this->_modelName);
        $canSave = $this->setPropertiesFromPost($model);
        if ($canSave) {
            $fileInfo = $this->getRequest()->files['content'];
            if (! empty($fileInfo['tmp_name'])) {
                $fileInfo = $this->getRequest()->files['content'];
                $model->content = file_get_contents($fileInfo['tmp_name']);
                $model->name = $fileInfo['name'];
                $model->type = $fileInfo['type'];
            }
            
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
    
    protected function innerActionDoEdit() {
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        
        if ($item) {
            $canSave = $this->setPropertiesFromPost($item);
            
            if ($canSave) {
                $fileInfo = $this->getRequest()->files['content'];
                if (! empty($fileInfo['tmp_name'])) {
                    $item->content = file_get_contents($fileInfo['tmp_name']);
                    $item->name = $fileInfo['name'];
                    $item->type = $fileInfo['type'];
                }
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
    
    protected function innerActionView() {
        if (array_key_exists('view', $this->_templateNames)) {
            $this->getView()->setTemplate($this->_templateNames['view']);
        }
        
        $conditionsList = $this->_conditionsList;
        $conditionsList['id'] = $this->_id;
        $item = null;
        $model = Factory::instance()->createModel($this->_modelName);
        $item = $model->getOneModel($conditionsList);
        $item->content = '<a href="' . $this->getRootUrl() . $this->_downloadUrl . '/' . $item->url . '">' . $item->name . '</a>';
        $item->name = '<a href="' . $this->getRootUrl() . $this->_downloadUrl . '/redirect/' . $item->url . '">' . $item->name . '</a>';
        
        if ($item) {
            $this->getView()->set('item', $item);
            $content = $this->getView()->render();
        } else {
            $this->setStashData('messageType', 'itemNotFound');
            $this->redirect($this->getBaseUrl());
        }
        
        return $content;
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
        $currentPage = (string) $currentPage;
        
        $itemsList = array();
        $model = Factory::instance()->createModel($this->_modelName);
        $itemsList = $model->getModelsList(
            $this->_conditionsList,
            $this->_itemsPerPage,
            ((int) $currentPage - 1) * (int) $this->_itemsPerPage,
            $this->_sortingList
        );
        
        foreach ($itemsList as $item) {
            $item->content = '<a href="' . $this->getRootUrl() . $this->_downloadUrl . '/' . $item->url . '">' . $item->name . '</a>';
            $item->name = '<a href="' . $this->getRootUrl() . $this->_downloadUrl . '/redirect/' . $item->url . '">' . $item->name . '</a>';
        }
        
        $modelsCount = $model->getCount(
            $this->_conditionsList
        );
        
        $pagesList = array();
        if ($modelsCount > 1) {
            $pagesCount = ceil($modelsCount / $this->_itemsPerPage);
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
    
}
