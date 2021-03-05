<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Auth\Auth;

/**
 * Model file.
 * 
 * @property string|null $id Id.
 * @property string|null $content File content. 
 * @property string|null $name File name.
 * @property string|null $type MIME file type.
 * @property string|null $url Url, started from "/" .
 * @property string|null $comment Comment.
 * @property bool $accessAdmin Access rights for viewing.
 * @property bool $accessSeller Access rights for viewing.
 * @property bool $accessBuyer Access rights for viewing.
 * @property bool $accessGuest Access rights for viewing.
 * @property bool $disabled Is user disabled.
 * @property bool $deleted Is user deleted.
 */
class ModelFile extends ModelAccessRights {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'file';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'content', 'caption' => 'Файл'),
        array('name' => 'name', 'caption' => 'Имя файла'),
        array('name' => 'type', 'caption' => 'MIME тип файла'),
        array('name' => 'url', 'caption' => 'Uri'),
        array('name' => 'comment', 'caption' => 'Комментарий'),
        array('name' => 'accessAdmin', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна админам'),
        array('name' => 'accessSeller', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна продавцам'),
        array('name' => 'accessBuyer', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна покупателям'),
        array('name' => 'accessGuest', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна гостям'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'caption' => 'Отключена'),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
}
