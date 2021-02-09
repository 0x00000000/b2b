<?php

declare(strict_types=1);

namespace B2bShop\Controller\Admin;

use B2bShop\Module\Factory\Factory;

class ControllerAdminSettings extends ControllerAdminBase {
    
    /**
     * Request action.
     */
    protected $_action = null;
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should be started from '/'.
     */
    protected $_innerUrl = '/admin/settings';
    
    /**
     * @var array $_templateNames View templates' names.
     */
    protected $_templateNames = array(
        'edit' => 'Common/Admin/Settings/edit',
        'view' => 'Common/Admin/Settings/view',
    );
    
    protected function actionIndex() {
        
        $get = $this->getRequest()->get;
        if (array_key_exists('action', $get)) {
            $this->_action = $get['action'];
        }
        
        if ($this->_action === 'edit') {
            $content = $this->innerActionEdit();
        } else if (empty($this->_action)) {
            $content = $this->innerActionView();
        } else {
            $this->send404();
        }
        
        return $content;
    }
    
    protected function innerActionEdit() {
        if ($this->getFromPost('submit')) {
            $this->innerActionDoEdit();
        }
        
        $this->getView()->setTemplate($this->_templateNames['edit']);
        
        $settings = Factory::instance()->createModel('Settings')->getSettings();
        $this->getView()->set('settings', $settings);
        $content = $this->getView()->render();
        
        return $content;
    }
    
    protected function innerActionDoEdit() {
        $settings = Factory::instance()->createModel('Settings')->getSettings();
        
        $canSave = $this->setPropertiesFromPost($settings);
            
        if ($canSave) {
            if ($item->save()) {
                $this->setStashData('messageType', 'editedSuccessfully');
            } else {
                $this->setStashData('messageType', 'editingFailed');
            }
        } else {
            $this->setStashData('messageType', 'editingFailed');
        }
        
        $this->redirect($this->getBaseUrl());
    }
    
    protected function innerActionView() {
        $this->getView()->setTemplate($this->_templateNames['view']);
        
        $settings = Factory::instance()->createModel('Settings')->getSettings();
        $this->getView()->set('settings', $settings);
        $content = $this->getView()->render();
        
        return $content;
    }
    
}
