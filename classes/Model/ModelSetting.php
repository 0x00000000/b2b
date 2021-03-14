<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Factory\Factory;

/**
 * Model for storing settings.
 * 
 * @property string|null $id Id.
 * @property string|null $name Setting name.
 * @property string|null $caption Setting caption.
 * @property string|null $value Setting value.
 * @property bool $disabled Is user disabled.
 * @property bool $deleted Is user deleted.
 */
class ModelSetting extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'setting';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'name', 'caption' => 'Имя'),
        array('name' => 'caption', 'caption' => 'Заголовок'),
        array('name' => 'value', 'caption' => 'Значение'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'caption' => 'Отключён'),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'caption' => 'Удалён'),
    );
    
}
