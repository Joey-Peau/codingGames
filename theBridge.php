<?php
/**
 * Coding challenge : https://www.codingame.com/ide/puzzle/the-bridge-episode-2
 * PHP : 7.2
 *
 * @version 0.2
 * pass 2/10 tests
 */

/** @noinspection SelfClassReferencingInspection */

/**
 * Compatible PHP 7.2
 */
class Bridge
{
    /** @var RoadLane[] */
    private $roadLanes = [];

    /**
     * Bridge constructor.
     *
     * @param  string[]  $roadsSettings
     */
    public function __construct(array $roadsSettings)
    {
        for ($i = 0, $iMax = count($roadsSettings); $i < $iMax; $i++) {
            $this->roadLanes[$i] = new RoadLane($this, $i, $roadsSettings[$i]);
        }
    }

    /** @var MotorBike[] */
    private $bikesAlive = [];

    public function addBike(MotorBike $bike)
    {
        $this->bikesAlive[] = $bike;
    }

    public function totalBikesAlive()
    {
        return count($this->bikesAlive);
    }

    public function getLanes()
    {
        return $this->roadLanes;
    }

    /**
     * from 0 (top) to n (bottom)
     *
     * @param  int  $index
     *
     * @return RoadLane|null
     */
    public function getRoadLaneIndex(int $index)
    {
        return $this->roadLanes[$index] ?? null;
    }
}

class RoadLane
{
    /** @var bool[] */
    private $isSafePosition;

    /** @var Bridge */
    private $bridge;

    /** @var int */
    private $laneIndex;

    /** @var MotorBike|null */
    private $motorBike;

    public function getPositions()
    {
        return $this->isSafePosition;
    }

    /**
     * @param  \MotorBike|null  $bike
     *
     * @todo add description
     */
    public function setMotorBike(?MotorBike $bike)
    {
        $this->motorBike = $bike;
    }

    public function __construct(Bridge $bridge, int $laneIndex, string $settings)
    {
        $this->bridge = $bridge;
        $this->laneIndex = $laneIndex;
        $endPosition = strlen($settings) - 1;

        for ($i = 0; $i <= $endPosition; $i++) {
            $this->isSafePosition[$i] = $settings[$i] === '.';
        }
    }

    /**
     * Check if it a road or a hole
     *
     * @param  int  $index
     *
     * @return bool
     */
    public function isSafePosition(int $index)
    {
        return $this->isSafePosition[$index] ?? false;
    }

    /**
     * @return \RoadLane|null
     * @todo add description
     */
    public function getUpperLane()
    {
        return $this->bridge->getRoadLaneIndex($this->laneIndex - 1);
    }

    /**
     * @return \RoadLane|null
     * @todo add description
     */
    public function getLowerLane()
    {
        return $this->bridge->getRoadLaneIndex($this->laneIndex + 1);
    }

    /**
     * @return bool
     * @todo add description
     */
    public function isOccupied()
    {
        return $this->motorBike !== null;
    }

    /**
     * @return \MotorBike|null
     * @todo add description
     */
    public function getOccupingMotorBike()
    {
        return $this->motorBike;
    }
}

/**
 * Class MotorBike
 */
class MotorBike
{
    /** @var \RoadLane */
    public $currentLane;

    public const __MAX_SPEED = 50;

    /** @var int */
    private $speed = 0;

    public function __construct(RoadLane $currentLane)
    {
        $this->currentLane = $currentLane;
        $this->currentLane->setMotorBike($this);
    }

    public function __destruct()
    {
        $this->currentLane->setMotorBike(null);
    }

    /**
     * @throws \Exception When cannot speed up
     */
    public function speedUp()
    {
        if ($this->speed >= MotorBike::__MAX_SPEED) {
            throw new Exception('Cannot go faster');
        }
        $this->speed++;
    }

    /**
     * @throws \Exception When cannot slow Down
     */
    public function slowDown()
    {
        if ($this->speed <= 1) {
            throw new Exception('Cannot Go Slower');
        }
        $this->speed--;
    }

    /**
     * @param  \RoadLane  $newLane
     * @param  string     $functionCallable
     *
     * @throws \ErrorException
     */
    private function goToNewLane(RoadLane $newLane, string $functionCallable)
    {
        $occupingMotorBike = $newLane->getOccupingMotorBike();
        if ($occupingMotorBike !== null) {
            if (!method_exists($occupingMotorBike, $functionCallable)) {
                throw new ErrorException("function $functionCallable does not exists in " . get_class($occupingMotorBike));
            }
            $occupingMotorBike->$functionCallable();
        }

        $this->switchLane($newLane);
    }

    /**
     * @throws \Exception
     * @throws \ErrorException
     */
    public function goUpperLane()
    {
        $topLane = $this->currentLane->getUpperLane();
        if ($topLane === null) {
            throw new Exception('No lane to go to');
        }
        $this->goToNewLane($topLane, __FUNCTION__);
    }

    /**
     * @throws \Exception
     * @throws \ErrorException
     */
    public function goLowerLane()
    {
        $bottomLane = $this->currentLane->getLowerLane();
        if ($bottomLane === null) {
            throw new Exception('No lane to go to');
        }
        $this->goToNewLane($bottomLane, __FUNCTION__);
    }

    /**
     * @param  \RoadLane  $newLane
     */
    private function switchLane(RoadLane $newLane)
    {
        $newLane->setMotorBike($this);
        $this->currentLane->setMotorBike(null);
        $this->currentLane = $newLane;
    }

}

class Game
{

    public const __DIRECTIVE_SPEED = 'SPEED';
    public const __DIRECTIVE_SLOW  = 'SLOW';
    public const __DIRECTIVE_JUMP  = 'JUMP';
    public const __DIRECTIVE_WAIT  = 'WAIT';
    public const __DIRECTIVE_UP    = 'UP';
    public const __DIRECTIVE_DOWN  = 'DOWN';

    private const __MAX_TURNS = 50;

    private $globalSpeed = 0;
    private $currentYAxis = 0;

    /** @var int */
    private $minSurvive;

    /** @var \Bridge */
    private $bridge;

    public function __construct(int $minSurvive = 0, array $bridgeSettings = [])
    {
        $this->minSurvive = $minSurvive;

        $this->bridge = new Bridge($bridgeSettings);
    }

    public function setSpeed(int $speed)
    {
        $this->globalSpeed = $speed;
    }

    /**
     * @return string
     * @todo add description
     */
    public function giveDirective()
    {
        $bestAlive = 0;
        $bestDirective = Game::__DIRECTIVE_WAIT;

        $testSpeed = $this->testSpeedUp();
        if ($testSpeed > $bestAlive) {
            $bestAlive = $testSpeed;
            $bestDirective = Game::__DIRECTIVE_SPEED;
        }

        $testJump = $this->testJump();
        if ($testJump > $bestAlive) {
            $bestAlive = $testJump;
            $bestDirective = Game::__DIRECTIVE_JUMP;
        }

        $testGoUp = $this->testGoUp();
        if ($testGoUp > $bestAlive) {
            $bestAlive = $testGoUp;
            $bestDirective = Game::__DIRECTIVE_UP;
        }

        $testGoDown = $this->testGoDown();
        if ($testGoDown > $bestAlive) {
            $bestAlive = $testGoDown;
            $bestDirective = Game::__DIRECTIVE_DOWN;
        }

        $testWait = $this->testWait();
        if ($testWait > $bestAlive) {
            $bestAlive = $testWait;
            $bestDirective = Game::__DIRECTIVE_WAIT;
        }

        $testSlow = $this->testSlowDown();
        if ($testSlow > $bestAlive) {
            $bestAlive = $testSlow;
            $bestDirective = Game::__DIRECTIVE_SLOW;
        }

        if ($bestDirective === Game::__DIRECTIVE_SPEED) {
            $this->globalSpeed++;
        }

        if ($bestDirective === Game::__DIRECTIVE_SLOW) {
            $this->globalSpeed--;
        }

        $this->currentYAxis = $this->currentYAxis + $this->globalSpeed;

        return $bestDirective;
    }

    /**
     * @return \Bridge
     * @todo add description
     */
    public function getBridge()
    {
        return $this->bridge;
    }

    private function testWait()
    {
        $alive = $this->bridge->totalBikesAlive();

        foreach ($this->bridge->getLanes() as $lane) {
            if (!$lane->isOccupied()) {
                continue;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed; $position++) {
                if (!$lane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }
        }

        return $alive;
    }

    private function testSpeedUp()
    {
        $alive = $this->bridge->totalBikesAlive();

        foreach ($this->bridge->getLanes() as $lane) {
            if (!$lane->isOccupied()) {
                continue;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed + 1; $position++) {
                if (!$lane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }
        }

        return $alive;
    }

    private function testSlowDown()
    {
        $alive = $this->bridge->totalBikesAlive();

        foreach ($this->bridge->getLanes() as $lane) {
            if (!$lane->isOccupied()) {
                continue;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed - 1; $position++) {
                if (!$lane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }
        }

        return $alive;
    }

    private function testGoUp()
    {
        $alive = $this->bridge->totalBikesAlive();

        $totalLanes = count($this->bridge->getLanes());

        $canGoUp = true;
        //from top to bottom
        for ($index = 0; $index < $totalLanes; $index++) {
            $testableLane = $this->bridge->getRoadLaneIndex($index);
            if ($testableLane === null) {
                continue;
            }

            if (!$testableLane->isOccupied()) {
                continue;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed - 1; $position++) {
                if (!$testableLane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }

            $upperLane = $testableLane->getUpperLane();
            if ($upperLane === null) {
                $canGoUp = false;
            }

            if ($canGoUp) {
                $testableLane = $upperLane;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed; $position++) {
                if (!$testableLane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }
        }

        return $alive;
    }

    private function testGoDown()
    {
        $alive = $this->bridge->totalBikesAlive();

        $totalLanes = count($this->bridge->getLanes());

        $canGoDown = true;
        //from top to bottom
        for ($index = $totalLanes - 1; 0 <= $index; $index--) {
            $testableLane = $this->bridge->getRoadLaneIndex($index);
            if ($testableLane === null) {
                continue;
            }

            if (!$testableLane->isOccupied()) {
                continue;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed - 1; $position++) {
                if (!$testableLane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }

            $lowerLane = $testableLane->getLowerLane();
            if ($lowerLane === null) {
                $canGoDown = false;
            }

            if ($canGoDown) {
                $testableLane = $lowerLane;
            }

            for ($position = $this->currentYAxis; $position <= $this->currentYAxis + $this->globalSpeed; $position++) {
                if (!$testableLane->isSafePosition($position)) {
                    $alive--;
                    continue 2;
                }
            }
        }

        return $alive;
    }

    private function testJump()
    {
        $alive = $this->bridge->totalBikesAlive();

        $totalLanes = count($this->bridge->getLanes());

        //from top to bottom
        for ($index = $totalLanes - 1; 0 <= $index; $index--) {
            $testableLane = $this->bridge->getRoadLaneIndex($index);
            if ($testableLane === null) {
                continue;
            }

            if (!$testableLane->isOccupied()) {
                continue;
            }

            if (!$testableLane->isSafePosition($this->currentYAxis + $this->globalSpeed)) {
                $alive--;
            }
        }

        return $alive;
    }
}

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

// $M: the amount of motorbikes to control
fscanf(STDIN, "%d", $M);
// $V: the minimum amount of motorbikes that must survive
fscanf(STDIN, "%d", $V);
// $L0: L0 to L3 are lanes of the road. A dot character . represents a safe space, a zero 0 represents a hole in the road.
fscanf(STDIN, "%s", $L0);
fscanf(STDIN, "%s", $L1);
fscanf(STDIN, "%s", $L2);
fscanf(STDIN, "%s", $L3);

$game = new Game($V, [$L0, $L1, $L2, $L3]);

$firstIteration = true;
// game loop
while (true) {
    // $S: the motorbikes' speed
    fscanf(STDIN, "%d", $S);
    $game->setSpeed($S);
    for ($i = 0; $i < $M; $i++) {
        // $X: x coordinate of the motorbike
        // $Y: y coordinate of the motorbike
        // $A: indicates whether the motorbike is activated "1" or detroyed "0"
        fscanf(STDIN, "%d %d %d", $X, $Y, $A);
        if ($firstIteration) {
            $bridge = $game->getBridge();
            $lane = $bridge->getRoadLaneIndex($Y);
            $bike = new MotorBike($lane);
            $bridge->addBike($bike);
            $firstIteration = false;
        }
    }

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)

    // A single line containing one of 6 keywords: SPEED, SLOW, JUMP, WAIT, UP, DOWN.
    $directive = $game->giveDirective();
    echo("$directive\n");
}

