<?php

declare(strict_types=1);

namespace B2bShop\System;

/**
 * Functionality for work with file system.
 */
class FileSystem {
    
    /**
     * @var string|null $_root File system path to script's root directory.
     */
    private static $_root = null;
    
    /**
     * Gets file system path to script's root directory.
     * 
     * @return string Script's root directory.
     */
    public static function getRoot(): string {
        if (! self::$_root) {
            self::$_root = dirname(dirname(dirname(__FILE__)));
        }
        
        return self::$_root;
    }
    
    /**
     * Gets file system path to writable directory for data.
     * 
     * @return string Data directory or null if it is absent.
     */
    public static function getDataDir(): ?string {
        $dir = self::getRoot() . self::getDS() . 'data';
        if (! is_dir($dir)) {
            $dir = null;
        }
        
        return $dir;
    }
    
    /**
     * Gets directory separator.
     * 
     * @return string Directory separator.
     */
    public static function getDirectorySeparator(): string {
        return DIRECTORY_SEPARATOR;
    }
    
    /**
     * Gets directory separator.
     * 
     * @return string Directory separator.
     */
    public static function getDS(): string {
        return DIRECTORY_SEPARATOR;
    }
    
    /**
     * Gets php scripts extension used in this project.
     * 
     * @return string Scripts extension.
     */
    public static function getScriptExtension(): string {
        return '.php';
    }
    
}