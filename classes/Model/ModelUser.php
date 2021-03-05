<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Config\Config;
use B2bShop\Module\Factory\Factory;

/**
 * Model user.
 * 
 * @property string|null $id User's id.
 * @property string|null $login User's login.
 * @property string|null $name User's name.
 * @property bool $isAdmin Is user admin.
 * @property bool $isSeller Is user a seller.
 * @property bool $isBuyer Is user a buyer.
 * @property bool $disabled Is user disabled.
 * @property bool $deleted Is user deleted.
 * 
 * Property only to set:
 * @property string|null $password User's password (writeonly).
 */
class ModelUser extends ModelDatabase {
    /**
     * Default url if $_SERVER['SERVER_NAME'] is not set.
     */
    public const UNKNOWN_SERVER_NAME = 'UNKNOWN_SERVER_NAME';
    
    /**
     * User's access levels.
     */
    public const ACCESS_ADMIN = 1;
    public const ACCESS_SELLER = 2;
    public const ACCESS_BUYER = 4;
    public const ACCESS_GUEST = 8;
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'user';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'login', 'caption' => 'Логин'),
        array('name' => 'password', 'caption' => 'Пароль'),
        array('name' => 'name', 'caption' => 'ФИО'),
        array('name' => 'isAdmin', 'type' => self::TYPE_BOOL),
        array('name' => 'isSeller', 'type' => self::TYPE_BOOL),
        array('name' => 'isBuyer', 'type' => self::TYPE_BOOL),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'caption' => 'Отключён'),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'caption' => 'Удалён'),
    );
    
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Loads user from database by login if it is possible.
     */
    public function loadByLogin(string $login, string $password): bool {
        $result = false;
        
        $dbData = $this->getDataRecord(
            array(
                'login' => $login,
                'password' => $this->encodePassword($password),
                'disabled' => '0',
                'deleted' => '0'
            )
        );
        if ($dbData && count($dbData)) {
            $result = $this->setDataFromDB($dbData);
        }
        
        return $result;
    }
    
    /**
     * Checks existing user with login and password.
     */
    public function check(string $login, string $password): bool {
        $result = false;
        
        $dbData = $this->getDataRecord(
            array(
                'login' => $login,
                'password' => $this->encodePassword($password),
                'disabled' => '0',
                'deleted' => '0'
            )
        );
        if ($dbData && count($dbData)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Password can't be gotten.
     */
    public function getPassword() {
        return null;
    }
    
    /**
     * Sets hash instead of password.
     */
    public function setPassword($value) {
        $password = $this->encodePassword($value);
        $this->setRawProperty('password', $password);
    }
    
    /**
     * Encodes password.
     */
    protected function encodePassword($password) {
        $salt1 = Config::instance()->get('user', 'salt1');
        $salt2 = Config::instance()->get('user', 'salt2');
        return hash('sha512', $salt1 . $password . $salt2);
    }
    
    /**
     * Gets access levels values. May be used in controls.
     */
    public static function getAccessValues(): array {
        return array(
            self::ACCESS_ADMIN => 'admin',
            self::ACCESS_SELLER => 'seller',
            self::ACCESS_BUYER => 'buyer',
            self::ACCESS_GUEST => 'guest',
        );
    }
    
}
