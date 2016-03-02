<?php
namespace Drd\Tests\DiceRoll;

use Drd\DiceRoll\Dice;
use Drd\DiceRoll\DiceRoll;
use Drd\DiceRoll\DiceRollEvaluator;
use Drd\DiceRoll\Roll;
use Drd\DiceRoll\Roller;
use Drd\DiceRoll\RollOn;
use Granam\Integer\IntegerInterface;
use Granam\Tests\Tools\TestWithMockery;

class RollerTest extends TestWithMockery
{

    /**
     * @test
     */
    public function I_can_create_it()
    {
        $rollerWithMalus = new Roller(
            $dice = $this->createDice(),
            $numberOfStandardRolls = $this->createNumber(),
            $diceRollEvaluator = $this->createDiceRollEvaluator(),
            $bonusRollOn = $this->createBonusRollOn(),
            $malusRollOn = $this->createMalusRollOn()
        );
        $this->assertSame($dice, $rollerWithMalus->getDice());
        $this->assertSame($numberOfStandardRolls, $rollerWithMalus->getNumberOfStandardRolls());
        $this->assertSame($diceRollEvaluator, $rollerWithMalus->getDiceRollEvaluator());
        $this->assertSame($bonusRollOn, $rollerWithMalus->getBonusRollOn());
        $this->assertSame($malusRollOn, $rollerWithMalus->getMalusRollOn());
    }

    /**
     * @param int $minimumValue
     * @param int $maximumValue
     * @return \Mockery\MockInterface|Dice
     */
    private function createDice($minimumValue = 1, $maximumValue = 1)
    {
        $dice = $this->mockery(Dice::class);
        $dice->shouldReceive('getMinimum')
            ->andReturn($minimum = $this->mockery(IntegerInterface::class));
        $minimum->shouldReceive('getValue')
            ->andReturn($minimumValue);
        $dice->shouldReceive('getMaximum')
            ->andReturn($maximum = $this->mockery(IntegerInterface::class));
        $maximum->shouldReceive('getValue')
            ->andReturn($maximumValue);

        return $dice;
    }

    /**
     * @param int $number
     * @return \Mockery\MockInterface|IntegerInterface
     */
    private function createNumber($number = 1)
    {
        $numberOfStandardRolls = $this->mockery(IntegerInterface::class);
        $numberOfStandardRolls->shouldReceive('getValue')
            ->andReturn($number);

        return $numberOfStandardRolls;
    }

    /**
     * @return \Mockery\MockInterface|DiceRollEvaluator
     */
    private function createDiceRollEvaluator()
    {
        $diceRollEvaluator = $this->mockery(DiceRollEvaluator::class);
        $diceRollEvaluator->shouldReceive('evaluateDiceRoll')
            ->with(\Mockery::type(DiceRoll::class))
            ->andReturnUsing(function (DiceRoll $diceRoll) {
                return $diceRoll->getRolledNumber(); // de facto one to one
            });

        return $diceRollEvaluator;
    }

    /**
     * @param array|int $shouldHappenOn
     * @param int $numberOfDiceRolls = 1
     * @param Dice $dice = null
     * @return RollOn|\Mockery\MockInterface
     */
    private function createBonusRollOn($shouldHappenOn = [], $numberOfDiceRolls = 1, Dice $dice = null)
    {
        return $this->createRollOn($shouldHappenOn, $numberOfDiceRolls, $dice);
    }

    /**
     * @param array|int[] $shouldHappenOn
     * @param int $numberOfDiceRolls
     * @param Dice $dice = null
     * @return \Mockery\MockInterface|RollOn
     */
    private function createRollOn($shouldHappenOn, $numberOfDiceRolls, Dice $dice = null)
    {
        $rollOn = $this->mockery(RollOn::class);
        $rollOn->shouldReceive('shouldHappen')
            ->andReturnUsing(function ($value) use ($shouldHappenOn) {
                return in_array($value, $shouldHappenOn);
            });
        $rollOn->shouldReceive('rollDices')
            ->with(\Mockery::type('int'))
            ->andReturnUsing(function ($rollSequenceStart) use ($numberOfDiceRolls, $dice) {
                $this->assertGreaterThan(0, $rollSequenceStart);
                $diceRolls = [];
                for ($diceRollNumber = 1; $diceRollNumber <= $numberOfDiceRolls; $diceRollNumber++) {
                    $diceRoll = $this->mockery(DiceRoll::class);
                    $diceRoll->shouldReceive('getDice')
                        ->andReturn($dice);
                    $diceRoll->shouldReceive('getRollSequence')
                        ->andReturn($rolledNumber = $this->createNumber($rollSequenceStart + ($diceRollNumber - 1)));
                    $diceRoll->shouldReceive('getValue')
                        ->andReturn($diceRollNumber /* just some int for sum */);
                    $diceRolls[] = $diceRoll;
                }

                return $diceRolls;
            });
        $rollOn->shouldReceive('rollDices')
            ->andReturn($numberOfDiceRolls);

        return $rollOn;
    }

    /**
     * @param array $shouldHappenOn
     * @param int $numberOfDiceRolls
     * @param Dice $dice = null
     * @return RollOn|\Mockery\MockInterface
     */
    private function createMalusRollOn(array $shouldHappenOn = [], $numberOfDiceRolls = 1, Dice $dice = null)
    {
        return $this->createRollOn($shouldHappenOn, $numberOfDiceRolls, $dice);
    }

    /**
     * @test
     * @expectedException \Drd\DiceRoll\Exceptions\InvalidDiceRange
     */
    public function I_can_not_use_strange_dice_with_minimum_greater_than_maximum()
    {
        new Roller(
            $this->createDice(2, 1),
            $this->createNumber(),
            $this->createDiceRollEvaluator(),
            $this->createBonusRollOn(),
            $this->createMalusRollOn()
        );
    }

    /**
     * @test
     * @expectedException \Drd\DiceRoll\Exceptions\InvalidNumberOfRolls
     */
    public function I_can_not_use_zero_number_of_standard_rolls()
    {
        new Roller(
            $this->createDice(),
            $this->createNumber(0),
            $this->createDiceRollEvaluator(),
            $this->createBonusRollOn(),
            $this->createMalusRollOn()
        );
    }

    /**
     * @test
     * @expectedException \Drd\DiceRoll\Exceptions\InvalidNumberOfRolls
     */
    public function I_can_not_use_negative_number_of_standard_rolls()
    {
        new Roller(
            $this->createDice(),
            $this->createNumber(-1),
            $this->createDiceRollEvaluator(),
            $this->createBonusRollOn(),
            $this->createMalusRollOn()
        );
    }

    /**
     * @test
     * @expectedException \Drd\DiceRoll\Exceptions\BonusAndMalusChanceConflict
     */
    public function I_can_not_use_bonus_and_malus_with_same_triggering_values()
    {
        new Roller(
            $this->createDice(1, 5),
            $this->createNumber(),
            $this->createDiceRollEvaluator(),
            $this->createBonusRollOn([2, 3]),
            $this->createMalusRollOn([3, 4])
        );
    }

    /**
     * @test
     */
    public function I_can_roll()
    {
        $roller = new Roller(
            $dice = $this->createDice($minimumValue = 111, $maximumValue = 222),
            $this->createNumber($numberOfRollsValue = 5),
            $this->createDiceRollEvaluator(),
            $bonusRollOn = $this->createBonusRollOn(),
            $malusRollOn = $this->createMalusRollOn()
        );
        $roll = $roller->roll();

        $this->checkSummaryAndRollSequence($roll, $dice, $numberOfRollsValue);
        $this->assertGreaterThanOrEqual($minimumValue * $numberOfRollsValue, $roll->getValue());
        $this->assertLessThanOrEqual($maximumValue * $numberOfRollsValue, $roll->getValue());
        $this->assertSame($roll->getDiceRolls(), $roll->getStandardDiceRolls());
        $this->assertEquals([], $roll->getBonusDiceRolls());
        $this->assertEquals([], $roll->getMalusDiceRolls());
    }

    private function checkSummaryAndRollSequence(Roll $roll, Dice $expectedDice, $numberOfRolls, $rollSequenceOffset = 0)
    {
        $summary = 0;
        $rollNumber = 0;
        $currentRollSequence = 0 + $rollSequenceOffset;
        foreach ($roll->getDiceRolls() as $diceRoll) {
            $currentRollSequence++;
            $rollNumber++;
            $this->assertSame($expectedDice, $diceRoll->getDice());
            $summary += $diceRoll->getValue();
            $this->assertSame(
                $currentRollSequence,
                $diceRoll->getRollSequence()->getValue() /* integer from the mock */,
                "Roll sequence is not successive. Expected $currentRollSequence (including offset $rollSequenceOffset)."
            );
        }
        $this->assertSame($roll->getValue(), $summary);
        $this->assertSame($rollNumber, $numberOfRolls);
    }

    /**
     * @test
     */
    public function I_can_roll_with_bonus()
    {
        $roller = new Roller(
            $dice = $this->createDice($minimumValue = 5, $maximumValue = 13),
            $numberOfRolls = $this->createNumber($numberOfRollsValue = 1),
            $diceRollEvaluator = $this->createDiceRollEvaluator(),
            $bonusRollOn = $this->createBonusRollOn(
                [7, 10],
                $numberOfBonusRollsValue = 3,
                $dice
            ),
            $malusRollOn = $this->createMalusRollOn()
        );
        for ($attempt = 1; $attempt < 1000; $attempt++) {
            $roll = $roller->roll($attempt /* used as roll sequence start */);
            $this->checkSummaryAndRollSequence(
                $roll,
                $dice,
                $numberOfRollsValue + count($roll->getBonusDiceRolls()),
                $attempt - 1 /* used as sequence start offset */
            );
            if (count($roll->getBonusDiceRolls()) > 1) { // at least 1 positive bonus roll (+ last negative bonus roll)
                break;
            }
        }
        $this->assertLessThan(1000, $attempt);
    }

    /**
     * @test
     */
    public function I_can_roll_with_malus()
    {
        $roller = new Roller(
            $dice = $this->createDice($minimumValue = 5, $maximumValue = 13),
            $numberOfRolls = $this->createNumber($numberOfRollsValue = 1),
            $diceRollEvaluator = $this->createDiceRollEvaluator(),
            $bonusRollOn = $this->createBonusRollOn(),
            $malusRollOn = $this->createMalusRollOn(
                [6, 7, 11],
                $numberOfMalusRollsValue = 4,
                $dice
            )
        );
        for ($attempt = 1; $attempt < 1000; $attempt++) {
            $roll = $roller->roll($attempt /* used as a roll sequence start */);
            $this->checkSummaryAndRollSequence(
                $roll,
                $dice,
                $numberOfRollsValue + count($roll->getMalusDiceRolls()),
                $attempt - 1 /* used as sequence start offset */
            );
            if (count($roll->getMalusDiceRolls()) > 1) { // at least one positive malus roll (+1 negative malus roll)
                break;
            }
        }
        $this->assertLessThan(1000, $attempt);
    }

}
