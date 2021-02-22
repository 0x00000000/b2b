<?php

declare(strict_types=1);

namespace B2bShop\Model;

/**
 * Model question.
 * 
 * @property string|null $id Id.
 * @property string|null $question Question.
 * @property string|null $answer Answer.
 * @property bool $disabled Is question disabled.
 * @property bool $deleted Is question deleted.
 */
class ModelQuestion extends ModelDatabase {
    
    /**
     * @var string $_table Name of database table.
     */
    protected $_table = 'question';
    
    /**
     * @var array $_propertiesList List of properties.
     */
    protected $_propertiesList = array(
        array('name' => 'id'),
        array('name' => 'question', 'caption' => 'Вопрос'),
        array('name' => 'answer', 'caption' => 'Ответ'),
        array('name' => 'disabled', 'type' => self::TYPE_BOOL, 'skipControl' => true),
        array('name' => 'deleted', 'type' => self::TYPE_BOOL, 'skipControl' => true),
    );
    
}
