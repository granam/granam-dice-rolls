<?php
namespace Drd\DiceRoll\Templates\Numbers;

class Zero extends Number
{
    /**
     * @return Number|Zero
     */
    public static function getIt()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return self::getInstance(0);
    }
}