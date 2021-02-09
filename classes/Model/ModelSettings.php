<?php

declare(strict_types=1);

namespace B2bShop\Model;

/**
 * Model settings.
 * 
 * @property string|null $id Id.
 * @property string|null $productsPerPage Count products per page.
 */
class ModelSettings extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'settings';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'productsPerPage'),
    );
    
    /**
     * Get concrete settings model.
     * 
     * return ModelSettings.
     */
    public function getSettings() {
        $settings = $this->getOneModel(array());
        return $settings;
    }
}
