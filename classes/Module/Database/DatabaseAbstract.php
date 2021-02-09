<?php

declare(strict_types=1);

namespace B2bShop\Module\Database;

/**
 * Allow get and save data from/in database.
 */
abstract class DatabaseAbstract {
    
    /**
     * Gets data by primary key.
     */
    abstract public function getByPk(string $table, string $pk, string $primaryKey = 'id'): ?array;
    
    /**
     * Gets data for one or more records.
     */
    abstract public function getList(
        string $table, array $conditionsList,
        ?int $limit, ?int $offset, ?array $sortingList
    ): array;
    
    /**
     * Gets count of records.
     */
    abstract public function getCount(string $table, array $conditionsList): int;
    
    /**
     * Saves the record in the database.
     */
    abstract public function addRecord(string $table, array $data): ?string;
    
    /**
     * Updates the record in the database.
     */
    abstract public function updateRecord(string $table, array $data, string $primaryKey = 'id'): ?string;
    
    /**
     * Deletes one or more records from the database.
     */
    abstract public function delete(string $table, array $conditionsList): bool;
    
    /**
     * Deletes one record by primary key from the database.
     */
    abstract public function deleteRecord(string $table, string $pk, string $primaryKey = 'id'): bool;
    
    /**
     * Gets last error information.
     */
    abstract public function getLastError(): ?array;
    
    /**
     * Escapes string for safe using in sql statements.
     */
    abstract protected function escape(string $value): string;
    
}