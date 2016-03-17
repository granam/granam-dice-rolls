<?php
namespace Drd\Tests\DiceRoll;

use Drd\DiceRoll\DiceRollEvaluator;

class DiceRollEvaluatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function I_can_use_dice_roll_evaluator_interface()
    {
        self::assertTrue(interface_exists(DiceRollEvaluator::class));
        $reflection = new \ReflectionClass(DiceRollEvaluator::class);
        $methods = $reflection->getMethods();
        self::assertCount(1, $methods);
        self::assertTrue($reflection->hasMethod('evaluateDiceRoll'));
        $evaluateDiceRoll = new \ReflectionMethod(DiceRollEvaluator::class, 'evaluateDiceRoll');
        self::assertSame(1, $evaluateDiceRoll->getNumberOfParameters());
        self::assertSame(1, $evaluateDiceRoll->getNumberOfRequiredParameters());
        /** @var \ReflectionParameter $parameter */
        $parameter = current($evaluateDiceRoll->getParameters());
        self::assertSame('diceRoll', $parameter->getName());
    }
}
