<?php

declare(strict_types=1);

namespace B2bShop\Model;

use B2bShop\Module\Database\Database;

/**
 * Abstract model class.
 */
abstract class ModelAbstract {
    
    /**
     * Field types.
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_BOOL = 'bool';
    public const TYPE_INT = 'int';
    public const TYPE_FK = 'fk'; // foreign key
    public const TYPE_ENUM = 'enum';
    
    /**
     * Loads object's data by primary key.
     */
    abstract public function loadByPk(string $id): bool;
    
    /**
     * Saves object's data.
     */
    abstract public function save(): ?string;
    
    /**
     * Gets object's data as associative array.
     */
    abstract public function getDataAssoc(): array;
    
    /**
     * Sets database object.
     */
    abstract public function setDatabase(Database $database): bool;
    
    /**
     * Gets last error information.
     */
    abstract public function getLastError(): ?array;
    
    /**
     * Gets model name for this object.
     * This name may be used for creating models using factory.
     */
    abstract public function getModelName(): string;
    
}