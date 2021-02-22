<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\System\Core;

use B2bShop\Module\Database\Database;
use B2bShop\Module\Factory\Factory;

/**
 * Abstract model class that stores data in database.
 */
abstract class ModelDatabase extends Model {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = null;
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array();
    
    /**
     * @var ?Database $_database Database object.
     */
    private $_database = null;
    
    /**
     * @var array $_dbFieldsMap Database fields to model properties map.
     */
    private $_dbFieldsMap = array();
    
    /**
     * @var array $_propertiesData Properties data.
     */
    public $_propertiesData = array();
    
    /**
     * Class constructor.
     */
    public function __construct() {
        $this->initPropertiesList();
    }
    
    /**
     * Load object's data from database by primary key.
     */
    public function loadByPk(string $pk): bool {
        $result = false;
        
        if ($this->getDatabase()) {
            if ($this->_table && $pk) {
                $dbData = $this->getDatabase()->getByPk($this->_table, $pk);
                if ($dbData) {
                    $result = $this->setDataFromDB($dbData);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Gets data from DB.
     */
    protected function getDataList(
        array $conditionsList,
        ?int $limit = 0, ?int $offset = 0,
        ?array $sortingList = array('id' => 'desc')
    ): array {
        $result = array();
        
        if ($this->getDatabase()) {
            if ($this->_table) {
                $dbConditionsList = $this->prepareConditionsListForDB($conditionsList);
                
                $dbSortingList = $sortingList;
                if (! is_null($dbSortingList)) {
                    $dbSortingList = $this->prepareSortingListForDB($sortingList);
                }
                
                $dbDataList = $this->getDatabase()->getList(
                    $this->_table,
                    $dbConditionsList,
                    $limit,
                    $offset,
                    $dbSortingList
                );
                if ($dbDataList) {
                    // $dbDataList can be null
                    $result = $dbDataList;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Gets data for single record from DB.
     */
    protected function getDataRecord(array $conditionsList): array {
        $result = array();
        
        $dbDataList =  $this->getDataList(
            $conditionsList,
            1,
            0
        );
        if ($dbDataList && count($dbDataList)) {
            $result = $dbDataList[0];
        }
        
        return $result;
    }
    
    /**
     * Gets array of models.
     */
    public function getModelsList(
        array $conditionsList,
        ?int $limit = 0, ?int $offset = 0,
        ?array $sortingList = array('id' => 'desc')
    ): array {
        $result = array();
        
        $dbDataList = $this->getDataList(
            $conditionsList,
            $limit,
            $offset,
            $sortingList
        );
        if ($dbDataList && count($dbDataList)) {
            foreach ($dbDataList as $dbData) {
                $modelName = $this->getModelName();
                $model = Factory::instance()->createModel($modelName);
                $model->setDataFromDB($dbData);
                $result[] = $model;
            }
        }
        
        return $result;
    }
    
    /**
     * Gets one of model.
     */
    public function getOneModel(array $conditionsList): ?Model {
        $result = null;
        
        $dbData = $this->getDataRecord($conditionsList);
        if ($dbData && count($dbData)) {
            $modelName = $this->getModelName();
            $model = Factory::instance()->createModel($modelName);
            if ($model->setDataFromDB($dbData)) {
                $result = $model;
            }
        }
        
        return $result;
    }
    
    /**
     * Gets count of records.
     */
    public function getCount(array $conditionsList): int {
        $result = false;
        
        $dbConditionsList = $this->prepareConditionsListForDB($conditionsList);
        if ($this->getDatabase()) {
            if ($this->_table) {
                $result = $this->getDatabase()->getCount(
                    $this->_table,
                    $dbConditionsList
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Saves object's data to database.
     */
    public function save(): ?string {
        $result = null;
        if ($this->getDatabase()) {
            if ($this->_table) {
                $data = $this->getDataForDB();
                
                if (count($data)) {
                    if (! $this->getPk()) {
                        $pk = $this->getDatabase()->addRecord($this->_table, $data);
                        if ($pk) {
                            $this->setPk($pk);
                            $result = $pk;
                        }
                    } else {
                        $this->getDatabase()->updateRecord($this->_table, $data);
                        $result = $this->getPk();
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Adds several recurds to database.
     */
    public function saveMultiple($data): bool {
        $result = false;
        if ($this->getDatabase()) {
            if ($this->_table) {
                if (count($data)) {
                    foreach ($data as $rowNumber => $row) {
                        foreach ($row as $key => $value) {
                            if ($key !== $this->_propertiesList[$key]['dbField']) {
                                unset($data[$rowNumber][$key]);
                                $data[$rowNumber][$this->_propertiesList[$key]['dbField']] = $value;
                            }
                        }
                    }
                    
                    $result = $this->getDatabase()->addMultipleRecord(
                        $this->_table,
                        $data,
                        array('recordsSetCount' => 100)
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Deletes models by condition.
     */
    public function delete(array $conditionsList): bool {
        $result = false;
        
        if ($this->getDatabase()) {
            if ($this->_table) {
                if ($conditionsList) {
                    $result = $this->getDatabase()->delete($this->_table, $conditionsList);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Deletes models by condition.
     */
    public function deleteByPk(string $pk): bool {
        $result = false;
        
        if ($this->getDatabase()) {
            if ($this->_table && $pk) {
                $result = $this->getDatabase()->deleteRecord($this->_table, $pk);
            }
        }
        
        return $result;
    }
    
    /**
     * Gets last error information.
     */
    public function getLastError(): ?array {
        $result = null;
        
        if ($this->getDatabase()) {
            $result = $this->getDatabase()->getLastError();
        }
        
        return $result;
    }
    
    /**
     * Sets database object.
     */
    public function setDatabase(Database $database): bool {
        $result = false;
        if (is_object($database) && $database instanceof Database) {
            $this->_database = $database;
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Sets database object.
     */
    protected function getDatabase() {
        return $this->_database;
    }
    
    /**
     * Gets object's data as associative array.
     */
    public function getDataAssoc(): array {
        $data = array();
        
        foreach ($this->_propertiesList as $name => $property) {
            $data[$name] = $this->$name;
        }
        
        return $data;
    }
    
    /**
     * Gets data for current object. This data may be used for writing to database.
     */
    public function getDataForDB(): array {
        $dbData = array();
        
        foreach ($this->_propertiesData as $dbField => $value) {
            $dbData[$dbField] = $value;
        }
        
        return $dbData;
    }
    
    /**
     * Sets data from database to current object.
     */
    protected function setDataFromDB(array $dbData): bool {
        $result = false;
        $data = array();
        if (is_array($dbData)) {
            if (count($dbData)) {
                $result = true;
                $this->clearPropertiesData();
                
                foreach ($dbData as $dbField => $valueRaw) {
                    $propertyName = $this->getModelPropertyName($dbField);
                    if ($propertyName) {
                        $this->setRawProperty($propertyName, $valueRaw);
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Model properties can be set by this magic meghod.
     */
    public function __set(string $propertyName, $value): void {
        if ($this->isProperty($propertyName)) {
            $methodName = 'set' . ucfirst($propertyName);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            } else {
                $valuePrepared = $this->preparePropertyForDB($propertyName, $value);
                $this->setRawProperty($propertyName, $valuePrepared);
            }
        }
    }
    
    /**
     * Model properties can be checked for existing by this magic meghod.
     */
    public function __isset(string $propertyName) {
        return ! is_null($this->$propertyName);
    }
    
    /**
     * Model properties can be get by this magic meghod.
     */
    public function __get(string $propertyName) {
        $result = null;
        
        if ($this->isProperty($propertyName)) {
            $methodName = 'get' . ucfirst($propertyName);
            if (method_exists($this, $methodName)) {
                $result = $this->$methodName();
            } else {
                $valueRaw = $this->getRawProperty($propertyName);
                $result = $this->preparePropertyFromDB($propertyName, $valueRaw);
            }
        }
        
        return $result;
    }
    
    /**
     * Prepare property before setting to object porperty.
     */
    protected function preparePropertyForDB($propertyName, $value) {
        $preparedValue = null;
        
        if ($this->isProperty($propertyName)) {
            $type = $this->_propertiesList[$propertyName]['type'];
            switch ($type) {
                case self::TYPE_BOOL:
                    $preparedValue = (string) (int) $value;
                    break;
                case self::TYPE_INT:
                    $preparedValue = (string) $value;
                    break;
                case self::TYPE_FK:
                    $preparedValue = (string) $value;
                    if (strlen($preparedValue) === 0) {
                        // We can't set empty strings as empty fk values.
                        $preparedValue = null;
                    }
                    break;
                default:
                    $preparedValue = (string) $value;
                    break;
            }
        }
        
        return $preparedValue;
    }
    
    /**
     * Prepare property before getting from object porperty.
     */
    protected function preparePropertyFromDB($propertyName, $value) {
        $preparedValue = null;
        
        if (! is_null($value)) {
            if ($this->isProperty($propertyName)) {
                $type = $this->_propertiesList[$propertyName]['type'];
                switch ($type) {
                    case self::TYPE_BOOL:
                        $preparedValue = (int) $value;
                        break;
                    case self::TYPE_INT:
                        $preparedValue = (int) $value;
                        break;
                    default:
                        $preparedValue = (string) $value;
                        break;
                }
            }
        }
        
        return $preparedValue;
    }
    
    /**
     * Checks if $propertyName is model property
     * and it can be used from outside.
     */
    protected function isProperty($propertyName) {
        $result = false;
        
        if (array_key_exists($propertyName, $this->_propertiesList)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Gets promary key value.
     */
    public function getPk() {
        return $this->id;
    }
    
    /**
     * Sets promary key value.
     */
    protected function setPk($pk) {
        $this->id = $pk;
    }
    
    /**
     * Checks if property is primary key.
     */
    public function isPk($propertyName) {
        return $propertyName === 'id';
    }
    
    /**
     * Inits properties list.
     */
    private function initPropertiesList() {
        $extendedList = array();
        
        if (is_array($this->_propertiesList)) {
            foreach ($this->_propertiesList as $property) {
                if (is_array($property)) {
                    $extended = $this->getExtendedProperty($property);
                    if ($extended) {
                        $extendedList[$extended['name']] = $extended;
                        $this->_dbFieldsMap[$extended['dbField']] = $extended['name'];
                    }
                }
            }
        }
        
        if (! array_key_exists('id', $extendedList)) {
            $property = array('name' => 'id',);
            $extended = $this->getExtendedProperty($property);
            if ($extended) {
                $extendedList[$extended['name']] = $extended;
                $this->_dbFieldsMap[$extended['dbField']] = $extended['name'];
            }
        }
        
        $this->_propertiesList = $extendedList;
    }
    
    /**
     * Inits property.
     */
    private function getExtendedProperty(array $property): ?array {
        $extended = null;
        
        if (array_key_exists('name', $property)) {
            $extended = $property;
            
            if (! array_key_exists('caption', $extended)) {
                $extended['caption'] = ucfirst($extended['name']);
            }
            
            if (! array_key_exists('dbField', $extended)) {
                $extended['dbField'] = $this->getDefaultDbFieldName($extended['name']);
            }
            
            if (! array_key_exists('type', $extended)) {
                $extended['type'] = self::TYPE_TEXT;
            }
            
        }
        
        return $extended;
    }
    
    /**
     * Transforms model property name to database field name.
     */
    private function getDefaultDbFieldName(string $propertyName): string {
        $fieldName = preg_replace_callback(
            '/[A-Z]/',
            function($matches) {
                return '_' . strtolower($matches[0]);
            },
            $propertyName
        );
        
        return $fieldName;
    }
    
    /**
     * Gets database property name by model property name.
     */
    private function getDbFieldName(string $propertyName): ?string {
        $fieldName = null;
        
        if ($this->isProperty($propertyName)) {
            $fieldName = $this->_propertiesList[$propertyName]['dbField'];
        }
        
        return $fieldName;
    }
    
    /**
     * Gets model property name by database property name.
     */
    protected function getModelPropertyName(string $fieldName): ?string {
        $propertyName = null;
        
        if (array_key_exists($fieldName, $this->_dbFieldsMap)) {
            $propertyName = $this->_dbFieldsMap[$fieldName];
        }
        
        return $propertyName;
    }
    
    protected function clearPropertiesData(): void {
        $this->_propertiesData = array();
    }
    
    protected function setRawProperty(string $propertyName, $value): bool {
        $result = false;
        
        if ($this->isProperty($propertyName)) {
            $result = true;
            $dbField = $this->getDbFieldName($propertyName);
            $this->_propertiesData[$dbField] = $value;
        }
        
        return $result;
    }
    
    protected function getRawProperty(string $propertyName) {
        $result = null;
        
        if ($this->isProperty($propertyName)) {
            $dbField = $this->getDbFieldName($propertyName);
            if (array_key_exists($dbField, $this->_propertiesData)) {
                $result = $this->_propertiesData[$dbField];
            }
        }
        
        return $result;
    }
    
    protected function prepareConditionsListForDB(array $conditionsList): array {
        $dbConditionsList = array();
        
        foreach ($conditionsList as $propertyName => $complexValue) {
            $dbField = $this->getDbFieldName($propertyName);
            if ($dbField) {
                $value = null;
                $conditionOperator = '=';
                if (is_array($complexValue) && array_key_exists('value', $complexValue)) {
                    $value = $complexValue['value'];
                    if (array_key_exists('condition', $complexValue)) {
                        // By default use '='.
                        $conditionOperator = $complexValue['condition'];
                    }
                } else if (! is_array($complexValue)) {
                    // Use '=' as condition by default.
                    $value = $complexValue;
                } else {
                    // Skip this.
                    $conditionOperator = null;
                }
                
                if ($conditionOperator) {
                    $dbValue = $this->preparePropertyForDB($propertyName, $value);
                    $dbConditionsList[$dbField] = array('value' => $dbValue, 'condition' => $conditionOperator);
                }
            }
        }
        
        return $dbConditionsList;
    }
    
    protected function prepareSortingListForDB(array $sortingList): array {
        $dbSortingList = array();
        
        foreach ($sortingList as $propertyName => $sortingValue) {
            $dbField = $this->getDbFieldName($propertyName);
            if ($dbField) {
                $dbSortingList[$dbField] = $sortingValue;
            }
        }
        
        return $dbSortingList;
    }
    
    public function getPropertiesList() {
        return $this->_propertiesList;
    }
    
    /**
     * Gets model name for this object.
     * This name may be used for creating models using factory.
     */
    public function getModelName(): string {
        $className = get_class($this);
        $modelName = preg_replace('/^' . Core::getNamespacePrefix() . '\\Model\\\\Model/', '', $className, 1);
        return $modelName;
    }
    
    /**
     * Format number with two decimal places.
     */
    protected function formatAsMoney($number) {
        return number_format((float) $number, 2, '.', '');
    }
    
}
