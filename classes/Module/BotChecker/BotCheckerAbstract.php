<?php

declare(strict_types=1);

namespace B2bShop\Module\BotChecker;

use B2bShop\Model\ModelQuestion;

/**
 * Bot checker class.
 */
abstract class BotCheckerAbstract {
    
    /**
     * Gets random question.
     */
    abstract public function getRandomQuestion(): ModelQuestion;
    
    /**
     * Checks if answer is correct.
     */
    abstract public function checkAnswer(string $questionId, string $questionAnswer): bool;
    
}
