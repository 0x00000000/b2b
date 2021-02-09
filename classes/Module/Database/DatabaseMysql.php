<?php

declare(strict_types=1);

namespace B2bShop\Module\Database;

use B2bShop\Module\Config\Config;

/**
 * Allow get and save data from/in mysql database.
 */
class DatabaseMysql extends Database {
    
    /**
     * @var string $_prefix Tables' prefix.
     */
    private $_prefix = '';
    
    /**
     * @var \mysqli $_mysqli mysqli object.
     */
    private $_mysqli = null;
    
    /**
     * Last executed query.
     */
    private $_lastQuery = null;
    
    /**
     * Allowed condition operators.
     */
    private $_allowedConditionOperators = array('=', '<', '>', '!=', '<=', '>=');
    
    /**
     * Class constructor.
     */
    public function __construct() {
        $config = Config::instance();
        $server = $config->get('database', 'server');
        $login = $config->get('database', 'login');
        $password = $config->get('database', 'password');
        $dbname = $config->get('database', 'name');
        $this->_prefix = $config->get('database', 'prefix');
        
        $this->_mysqli = new \mysqli($server, $login, $password, $dbname);
        if ($this->_mysqli->connect_errno) {
            die('Can\'t connect');
        }
        
        $this->_mysqli->query('set names UTF8');
    }
    
    /**
     * Gets data by primary key.
     */
    public function getByPk(string $table, string $pk, string $primaryKey = 'id'): ?array {
        $result = null;
        
        $query = 'select * from `' . $this->escape($this->_prefix . $table) . '`' 
            . ' where `' . $this->escape($primaryKey) . '` = "'
            . $this->escape($pk) . '"';
        $this->_lastQuery = $query;
        
        $res = $this->_mysqli->query($query);
        if ($res) {
            $row = $res->fetch_assoc();
            if ($row) {
                $result = $row;
            }
        }
        
        return $result;
    }
    
    /**
     * Gets data for one or more records.
     */
    public function getList(
        string $table, array $conditionsList,
        ?int $limit, ?int $offset, ?array $sortingList
    ): array {
        $result = array();
        
        $conditionQuery = $this->getConditionQuery($conditionsList);
        
        $sortingQuery = '';
        if (! is_null($sortingList) && count($sortingList)) {
            $sortingQueryList = array();
            foreach ($sortingList as $key => $value) {
                if (strcasecmp($value, 'desc') !== 0) {
                    $value = 'asc';
                }
                $sortingQueryList[] = '`' . $this->escape($key) . '` '
                    . $this->escape($value);
            }
            
            $sortingQuery = ' order by ' . implode(', ', $sortingQueryList);
        }
        
        $limitQuery = '';
        if ($limit) {
            if ($offset) {
                $limitQuery = ' limit ' . (string) $offset . ', ' . (string) $limit;
            } else {
                 $limitQuery = ' limit ' . (string) $limit;
            }
        }
        
        $query = 'select * from `' . $this->escape($this->_prefix . $table) . '`'
            . $conditionQuery
            . $sortingQuery
            . $limitQuery;
        $this->_lastQuery = $query;
        
        $res = $this->_mysqli->query($query);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $result[] = $row;
            }
        }
        
        return $result;
    }
    
    /**
     * Gets count of records.
     */
    public function getCount(string $table, array $conditionsList): int {
        $count = 0;
        
        $conditionQuery = $this->getConditionQuery($conditionsList);
        
        $query = 'select count(*) as count from `' . $this->escape($this->_prefix . $table) . '`'
            . $conditionQuery;
        $this->_lastQuery = $query;
        
        $res = $this->_mysqli->query($query);
        if ($res) {
            $row = $res->fetch_assoc();
            if (array_key_exists('count', $row)) {
                $count = (int) $row['count'];
            }
        }
        
        return $count;
    }
    
    /**
     * Saves the record to the database.
     */
    public function addRecord(string $table, array $data): ?string {
        $result = null;
        
        if (is_array($data) && count($data)) {
            $query = 'insert into ' . $this->escape($this->_prefix . $table);
            $keysArray = array();
            $valsArray = array();
            foreach ($data as $key => $val) {
                $keysArray[] = '`' . $this->escape($key) . '`';
                $valsArray[] = '"' . $this->escape($val) . '"';
            }
            
            $query .= ' (' . implode(', ', $keysArray) . ') ' . 
                ' values (' . implode(', ', $valsArray) . ')';
            $this->_lastQuery = $query;
            
            if ($this->_mysqli->query($query)) {
                $result = (string) $this->_mysqli->insert_id;
            }
        }
        
        return $result;
    }
    
    /**
     * Insert several records to the database.
     */
    public function addMultipleRecord(string $table, array $data, array $params): bool {
        $result = false;
        
        
        if (is_array($data) && is_array($data[0]) && count($data[0])) {
            $keysList = array();
            $keysQueryData = array();
            $valsQueryData = array();
            
            $recordsSetCount = ! empty($params['recordsSetCount']) ? $params['recordsSetCount'] : count($data);
            
            foreach ($data[0] as $key => $val) {
                $keysList[] = $key;
                $keysQueryData[] = '`' . $this->escape($key) . '`';
            }
            foreach ($data as $recordNumber => $recordData) {
                $recordSetKey = floor($recordNumber % $recordsSetCount);
                $valsQueryRow = array();
                foreach ($keysList as $key) {
                    if (isset($recordData[$key])) {
                        $valsQueryRow[] = '"' . $this->escape($recordData[$key]) . '"';
                    } else {
                        $valsQueryRow[] = '""';
                    }
                }
                $valsQueryData[$recordSetKey][] = '(' . implode(', ', $valsQueryRow) . ')';
            }
            
            foreach ($valsQueryData as $valsQueryDataSet) {
                $result = true;
                $query = 'insert into ' . $this->escape($this->_prefix . $table);
                $query .= ' (' . implode(', ', $keysQueryData) . ') ' . 
                    ' values ' . implode(', ', $valsQueryDataSet);
                $this->_lastQuery = $query;
                if (! $this->_mysqli->query($query)) {
                    $result = false;
                    break;
                }
            }
        }
        
        if (! $result) {
            var_export($this->getLastError()); exit;
            echo $this->_lastQuery;
        }
        return $result;
    }
    
    /**
     * Updates the record in the database.
     */
    public function updateRecord(string $table, array $data, string $primaryKey = 'id'): ?string {
        $result = null;
        
        if (is_array($data) && count($data) && array_key_exists($primaryKey, $data) && $data[$primaryKey]) {
            $query = 'update `' . $this->escape($this->_prefix . $table) . '` ';
            $valsArray = array();
            foreach ($data as $key => $val) {
                if ($key !== $primaryKey) {
                    if (! is_null($val)) {
                        $valsArray[] = '`' . $this->escape($key) . '`'
                            . ' = '
                            . '"' . $this->escape($val) . '"';
                    } else {
                        $valsArray[] = '`' . $this->escape($key) . '`' . ' = NULL';
                    }
                }
            }
            
            if (count($valsArray)) {
                $query .= ' set ' . implode(', ', $valsArray)
                    . ' where `' . $this->escape($primaryKey)
                    . '` = "' . $data[$primaryKey] . '"';
                $this->_lastQuery = $query;
                
                if ($this->_mysqli->query($query)) {
                    $result = (string) $data[$primaryKey];
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Deletes one or more records from the database.
     */
    public function delete(string $table, array $conditionsList): bool {
        $result = false;
        
        $conditionQuery = '';
        if (count($conditionsList)) {
            $conditionQueryList = array();
            foreach ($conditionsList as $key => $value) {
                $conditionQueryList[] = '`' . $this->escape($key) . '` = "'
                    . $this->escape($value) . '"';
            }
            
            $conditionQuery = '(' . implode(' and ', $conditionQueryList) . ')';
            
            $query = 'delete from `'
                . $this->escape($this->_prefix . $table)
                . '` where ' . $conditionQuery;
            $this->_lastQuery = $query;
            if ($this->_mysqli->query($query)) {
                $result = true;
            }
        }
        
        return $result;
    }
    
    /**
     * Deletes one record by primary key from the database.
     */
    public function deleteRecord(string $table, string $pk, string $primaryKey = 'id'): bool {
        $result = false;
        
        $query = 'delete from `'
            . $this->escape($this->_prefix . $table)
            . '` where `' . $this->escape($primaryKey)
            . '` = "' . $this->escape($pk) . '"';
        $this->_lastQuery = $query;
        if ($this->_mysqli->query($query)) {
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * Gets last error information.
     */
    public function getLastError(): ?array {
        $error = null;
        
        if ($this->_mysqli->errno !== 0) {
            $error = array();
            $error['error'] = $this->_mysqli->error;
            $error['code'] = $this->_mysqli->errno;
            if ($this->_lastQuery) {
                $error['query'] = $this->_lastQuery;
            }
        }
        
        return $error;
    }
    
    /**
     * Escapes string for safe using in sql statements.
     */
    protected function escape(string $value): string {
        $result = $this->_mysqli->escape_string((string) $value);
        
        return $result;
    }
    
    /**
     * Get query string by conditions.
     */
    private function getConditionQuery(array $conditionsList): string {
        $conditionQuery = '';
        
        if (count($conditionsList)) {
            $conditionQueryList = array();
            foreach ($conditionsList as $key => $value) {
                if (
                    is_array($value)
                    && isset($value['value'])
                    && isset($value['condition'])
                    && in_array($value['condition'], $this->_allowedConditionOperators)
                ) {
                    $conditionQueryList[] = '`' . $this->escape($key) . '`'
                        . ' ' . $value['condition'] . ' '
                        . '"' . $this->escape($value['value']) . '"';
                }
            }
            
            $conditionQuery = ' where (' . implode(' and ', $conditionQueryList) . ')';
        }
        
        return $conditionQuery;
    }

}
