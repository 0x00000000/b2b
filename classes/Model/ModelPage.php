<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Auth\Auth;

/**
 * Model page.
 * 
 * @property string|null $id Id.
 * @property string|null $caption Caption.
 * @property string|null $comment Comment.
 * @property string|null $url Url, started from "/" .
 * @property string|null $title Title. 
 * @property string|null $keywords Meta keywords. 
 * @property string|null $description Meta description.
 * @property string|null $content Page content (html).
 * @property bool $accessAdmin Access rights for viewing.
 * @property bool $accessSeller Access rights for viewing.
 * @property bool $accessBuyer Access rights for viewing.
 * @property bool $accessGuest Access rights for viewing.
 * @property bool $disabled Is user disabled.
 * @property bool $deleted Is user deleted.
 */
class ModelPage extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'page';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'caption', 'caption' => 'Название'),
        array('name' => 'comment', 'caption' => 'Комментарий'),
        array('name' => 'url', 'caption' => 'Uri'),
        array('name' => 'title', 'caption' => 'Заголовок'),
        array('name' => 'keywords', 'caption' => 'Ключевые слова'),
        array('name' => 'description', 'caption' => 'Описание'),
        array('name' => 'content', 'caption' => 'Контент'),
        array('name' => 'accessAdmin', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна админам'),
        array('name' => 'accessSeller', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна продавцам'),
        array('name' => 'accessBuyer', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна покупателям'),
        array('name' => 'accessGuest', 'type' => self::TYPE_BOOL, 'caption' => 'Доступна гостям'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'caption' => 'Отключена'),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
    public function userHasAccess(Auth $auth): bool {
        $result = (
            ($auth->isAdmin() && $this->accessAdmin)
            || ($auth->isSeller() && $this->accessSeller)
            || ($auth->isBuyer() && $this->accessBuyer)
            || ($auth->isGuest() && $this->accessGuest)
        );
        
        return $result;
    }
    
}
