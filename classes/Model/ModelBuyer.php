<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

/**
 * Model user.
 * 
 * @property string|null $id Buyer's id.
 * @property string $login Buyer's login.
 * @property string $name Buyer's name.
 * @property string $organization Buyer's organization.
 * @property string $phone Buyer's phone.
 * @property string $email Buyer's email.
 * @property string $address Buyer's address.
 * @property string $inn Buyer's inn.
 * @property bool $isAdmin Is user admin.
 * @property bool $isSeller Is user a seller.
 * @property bool $isBuyer Is user a buyer.
 * @property bool $isNew Is user a new buyer.
 * @property bool $disabled Is user disabled.
 * @property bool $deleted Is user deleted.
 * 
 * Property only to set:
 * @property string|null $password User's password (writeonly).
 */
class ModelBuyer extends ModelUser {
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'login', 'caption' => 'Логин'),
        array('name' => 'password', 'caption' => 'Пароль'),
        array('name' => 'name', 'caption' => 'ФИО'),
        array('name' => 'phone', 'caption' => 'Телефон'),
        array('name' => 'email', 'caption' => 'E-Mail'),
        array('name' => 'organization', 'caption' => 'Юридическое название'),
        array('name' => 'address', 'caption' => 'Адрес магазина или торговой точки'),
        array('name' => 'inn', 'caption' => 'ИНН'),
        array('name' => 'isAdmin', 'type' => self::TYPE_BOOL),
        array('name' => 'isSeller', 'type' => self::TYPE_BOOL),
        array('name' => 'isBuyer', 'type' => self::TYPE_BOOL),
        array('name' => 'isNew', 'type' => self::TYPE_BOOL, 'caption' => 'Новый'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'caption' => 'Отключён'),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'caption' => 'Удалён'),
    );
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    
    public function getIsBuyer(): int {
        return 1;
    }
    
    public function setIsBuyer($value) {
        $this->setRawProperty('isBuyer', 1);
    }
    
    public function getIsSeller(): int {
        return 0;
    }
    
    public function setIsSeller($value) {
        $this->setRawProperty('isSeller', 0);
    }
    
    public function getIsAdmin(): int {
        return 0;
    }
    
    public function setIsAdmin($value) {
        $this->setRawProperty('isAdmin', 0);
    }
    
    public function isLoginExisted(string $login): bool {
        $count = $this->getCount(array('login' => $login));
        return $count > 0;
    }
    
}
