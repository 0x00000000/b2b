<?php

declare(strict_types=1);

namespace B2bShop\Controller;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

use B2bShop\Model\ModelFile;

class ControllerFile extends ControllerBase {
    
    /**
     * @var array $_conditionsList Default conditions list.
     */
    protected $_conditionsList = array('deleted' => false, 'disabled' => false,);
    
    /**
     * @var array $_innerUrl Inner url to controller's root page. Should start from '/'.
     */
    protected $_innerUrl = '/file';
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    
    protected function actionIndex() {
        $url = $this->getRequest()->url;
        $urlPrefix = Config::instance()->get('application', 'urlPrefix');
        if ($urlPrefix) { // If application is not in site's root.
            $url = preg_replace('|^' . $urlPrefix . '|', '', $url);
        }
        
        $condition = array_merge($this->_conditionsList, array(/*'url' => $url*/));
        $file = Factory::instance()->createModel('File')
            ->getOneModel($condition);
        if ($file) {
            if ($this->checkAccess($file)) {
                $this->sendFile($file);
            } else {
                $this->redirect($this->getAuthUrl());
            }
        } else {
            $content = '';
            $this->send404();
        }
        
        return $content;
    }
    
    /**
     * Checks if current user has access to the file.
     */
    protected function checkAccess(ModelFile $file): bool {
        $result = $file->userHasAccess($this->getAuth());
        
        return $result;
    }
    
    /**
     * Sends file to user agent.
     */
    protected function sendFile(ModelFile $file) {
        $type = $file->type ? $file->type : 'application/octet-stream';
        header ('Content-Type: ' . $type);
        header ('Accept-Ranges: bytes');
        header ('Content-Length: ' . strlen($file->content));
        header ('Content-Disposition: attachment; filename=' . $file->name);
        echo $file->content;
        exit;
    }
    
}
