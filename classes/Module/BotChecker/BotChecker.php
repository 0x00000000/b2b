<?php

declare(strict_types=1);

namespace B2bShop\Module\BotChecker;

use B2bShop\Module\Factory\Factory;

use B2bShop\Model\ModelQuestion;

/**
 * Bot checker class.
 */
class BotChecker extends BotCheckerAbstract {
    
    /**
     * Gets random question.
     */
    public function getRandomQuestion(): ModelQuestion {
        $conditionsList = array('deleted' => false, 'disabled' => false);
        $modelQuestion = Factory::instance()->createModel('Question');
        $count = $modelQuestion->getCount($conditionsList);
        if ($count) {
            $number = rand(0, $count - 1);
            $modelsList = $modelQuestion->getModelsList($conditionsList, 1, $number);
            if (count($modelsList)) {
                $modelQuestion = $modelsList[0];
            }
        }
        return $modelQuestion;
    }
    
    /**
     * Checks if answer is correct.
     */
    public function checkAnswer(string $questionId, string $questionAnswer): bool {
        $result = false;
        
        if (strlen($questionId) && strlen($questionAnswer)) {
            $conditionsList = array(
                'deleted' => false,
                'disabled' => false,
                'id' => $questionId,
                'answer' => $questionAnswer,
            );
            $modelQuestion = Factory::instance()->createModel('Question');
            $count = $modelQuestion->getCount($conditionsList);
            $result = $count > 0;
        }
        
        return $result;
    }
    
}
