<?php
/**
 * Coding challenge : https://www.codingame.com/ide/puzzle/the-bridge-episode-2
 * PHP : 7.2
 *
 * @version 0.1
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

    /** @var int */
    private $minSurvive;

    /** @var \Bridge */
    private $bridge;

    public function __construct(int $minSurvive = 0, array $bridgeSettings = [])
    {
        $this->minSurvive = $minSurvive;

        $this->bridge = new Bridge($bridgeSettings);
    }

    /**
     * @return string
     * @todo add description
     */
    public function giveDirective()
    {
        return self::__DIRECTIVE_SPEED;
    }

    /**
     * @return \Bridge
     * @todo add description
     */
    public function getBridge()
    {
        return $this->bridge;
    }

    private function moveUp()
    {
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
    if ($firstIteration) {
        fscanf(STDIN, "%d", $S);
        for ($i = 0; $i < $M; $i++) {
            // $X: x coordinate of the motorbike
            // $Y: y coordinate of the motorbike
            // $A: indicates whether the motorbike is activated "1" or detroyed "0"
            fscanf(STDIN, "%d %d %d", $X, $Y, $A);
            $bike = new MotorBike();
            $bridge = $game->getBridge();
            $lane = $bridge->getLaneIndex($Y);
        }
        $firstIteration = false;
    }

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)

    // A single line containing one of 6 keywords: SPEED, SLOW, JUMP, WAIT, UP, DOWN.
    $directive = $game->giveDirective();
    echo("$directive\n");
}

