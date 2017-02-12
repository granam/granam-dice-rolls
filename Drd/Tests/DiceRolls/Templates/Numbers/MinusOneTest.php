<?php
namespace Drd\Tests\DiceRolls\Templates\Numbers;

use Drd\DiceRolls\Templates\Numbers\MinusOne;
use Granam\Integer\IntegerInterface;

class MinusOneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $minusOne = MinusOne::getIt();
        self::assertSame(-1, $minusOne->getValue());
        self::assertSame('-1', "$minusOne");
        self::assertInstanceOf(IntegerInterface::class, $minusOne);
    }
}