<?php
namespace Drd\DiceRoll\Templates\Dices;

use Drd\DiceRoll\Templates\Numbers\Four;
use Drd\DiceRoll\Templates\Numbers\One;

class Dice1d4 extends CustomDice
{
    private static $dice1d4;

    /**
     * @return Dice1d4
     */
    public static function getIt()
    {
        if (!isset(self::$dice1d4)) {
            self::$dice1d4 = new static();
        }

        return self::$dice1d4;
    }

    public function __construct()
    {
        parent::__construct(One::getIt(), Four::getIt());
    }
}
