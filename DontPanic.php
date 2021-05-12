<?php
/**
 * Coding challenge : https://www.codingame.com/ide/puzzle/don't-panic-episode-2
 * PHP : 7.2
 *
 * @version 1.2
 * pass 7/10 tests
 */

/** @noinspection SelfClassReferencingInspection */

/**
 * Compatible PHP 7.2
 */

const __WAIT = 'WAIT';
const __BLOCK = 'BLOCK';
const __ELEVATOR = 'ELEVATOR';

/**
 * Map model
 */
class Map
{
    /** @var Floor[] */
    private $floors = [];
    /** @var int */
    private $width;
    /** @var Floor */
    private $exit;
    /** @var int */
    private $exitPosition;
    /** @var Floor */
    private $startingFloor;
    /** @var int */
    private $startingPosition;

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    public function __construct(int $width, int $nbFloors, int $exitFloor, int $exitPosition, int $startingFloor = null, int $startingPosition = null)
    {
        $this->width = $width;
        for ($nb = 0; $nb < $nbFloors; $nb++) {
            $this->floors[$nb] = new Floor($this);
        }

        $this->exit = $this->floors[$exitFloor];
        $this->exitPosition = $exitPosition;

        $this->setStartingPoint($startingFloor, $startingPosition);
    }

    /**
     * @param  int|null  $startingFloor
     * @param  int|null  $startingPosition
     *
     * @return Map
     */
    public function setStartingPoint(?int $startingFloor, ?int $startingPosition)
    {
        if ($startingFloor !== null) {
            $this->startingFloor = $this->floors[$startingFloor];
        }

        if ($startingPosition !== null) {
            $this->startingPosition = $startingPosition;
        }

        return $this;
    }

    /**
     * @param  int  $floor
     *
     * @return \Floor
     */
    public function getUpperLevel(int $floor)
    {
        return $this->getFloor($floor + 1);
    }

    /**
     * @return \Floor
     */
    public function getStartingFloor()
    {
        return $this->startingFloor;
    }

    /**
     * @return int
     */
    public function getStartingPosition()
    {
        return $this->startingPosition;
    }

    /**
     * @param  int  $floor
     *
     * @return \Floor
     */
    public function getLowerLevel(int $floor)
    {
        return $this->getFloor($floor - 1);
    }

    /**
     * @param  int  $index
     *
     * @return Floor|null
     */
    public function getFloor(int $index)
    {
        return $this->floors[$index] ?? null;
    }

    /**
     * @return \Floor
     */
    public function getExitFloor()
    {
        return $this->exit;
    }

    /**
     * @return int
     */
    public function getExitPosition()
    {
        return $this->exitPosition;
    }
}

/**
 * LeadingBot Model
 */
class LeadingBot
{
    /** @var \Map */
    private $map;
    /** @var \Floor */
    private $currentFloor;
    /** @var int */
    private $currentPosition;
    /** @var string */
    private $direction;

    public const __DIRECTION_LEFT  = 'LEFT';
    public const __DIRECTION_RIGHT = 'RIGHT';

    public function __construct(Map $map, Floor $currentFloor, int $currentPosition, string $direction)
    {
        $this->map = $map;
        $this->currentFloor = $currentFloor;
        $this->currentPosition = $currentPosition;
        $this->direction = $direction;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->currentPosition;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return \Floor
     */
    public function getFloor()
    {
        return $this->currentFloor;
    }
}

/**
 * Floor Model
 */
class Floor
{
    /** @var int */
    private static $count = 0;
    /** @var int */
    private $indexFloor;
    /** @var Map */
    private $map;
    /** @var int[] */
    private $elevatorPositions;
    /** @var int|null */
    private $blockPosition = null;

    public function __construct(Map $map, array $elevators = [])
    {
        $this->map = $map;
        $this->elevatorPositions = $elevators;
        $this->indexFloor = self::$count++;
    }

    /**
     * @return \Floor|null
     */
    public function getUpperLevel()
    {
        return $this->map->getFloor($this->indexFloor + 1);
    }

    /**
     * @return \Floor|null
     */
    public function getLowerLevel()
    {
        return $this->map->getFloor($this->indexFloor - 1);
    }

    /**
     * @return int
     */
    public function getIndexFloor()
    {
        return $this->indexFloor;
    }

    /**
     * @param  int  $position
     */
    public function addElevator(int $position)
    {
        $this->elevatorPositions[] = $position;
    }

    /**
     * @param  int  $position
     *
     * @return bool
     */
    public function hasElevatorAtPosition(int $position)
    {
        return in_array($position, $this->elevatorPositions);
    }

    /**
     * @return \Map
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param  int  $position
     */
    public function blockPosition(int $position)
    {
        $this->blockPosition = $position;
    }

    /**
     * @return bool
     */
    public function hasBlockade()
    {
        return $this->blockPosition !== null;
    }

    /**
     * @return bool
     */
    public function hasElevators()
    {
        return count($this->elevatorPositions) > 0;
    }

    /**
     * @return int[]
     */
    public function getElevators()
    {
        return $this->elevatorPositions;
    }

    /**
     * @param  \LeadingBot  $bot
     * @param  int|null     $neededPosition
     *
     * @return bool
     */
    public function needBlockade(LeadingBot $bot, ?int $neededPosition = null)
    {
        if ($this->hasBlockade()) {
            return false;
        }

        if ($neededPosition === null) {
            return false;
        }

        if ($bot->getPosition() === $neededPosition) {
            return false;
        }

        //bot on the left and going left
        if ($bot->getPosition() < $neededPosition && $bot->getDirection() === LeadingBot::__DIRECTION_LEFT) {
            return true;
        }

        //bot on the right and going right
        if ($bot->getPosition() > $neededPosition && $bot->getDirection() === LeadingBot::__DIRECTION_RIGHT) {
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return $this->indexFloor . " => " . json_encode($this->elevatorPositions);
    }
}

/**
 * Main Game class
 */
class Game
{

    public const __DIRECTIVE_WAIT     = 'WAIT';
    public const __DIRECTIVE_BLOCK    = 'BLOCK';
    public const __DIRECTIVE_ELEVATOR = 'ELEVATOR';

    /** @var int */
    private $totalAdditional;

    /** @var array */
    private $optimalPath;

    /** @var \Map */
    private $map;

    public function __construct(int $nbFloors, int $width, int $nbRounds, int $exitFloor, int $exitPos, int $nbTotalClones, int $nbAdditionalElevators, int $nbElevators)
    {
        $this->totalAdditional = $nbAdditionalElevators;
        $this->map = new Map($width, $nbFloors, $exitFloor, $exitPos);
    }

    public function findBestPath()
    {
        $this->optimalPath = [];
        $this->buildPath($this->map->getExitFloor()->getIndexFloor(), $this->map->getExitPosition(), $this->optimalPath, $this->totalAdditional);
        error_log(var_export($this->optimalPath, true));
    }

    /**
     * finding best path from (top to bottom)
     *
     * @param  int    $currentFloorLevel
     * @param  int    $currentFloorPosition
     * @param  array  $potentialPath
     * @param  int    $availableElevatorsForLowerLevel
     *
     * @return int
     */
    private function buildPath(int $currentFloorLevel, int $currentFloorPosition, array &$potentialPath, int $availableElevatorsForLowerLevel)
    {
        $currentFloor = $this->map->getFloor($currentFloorLevel);

        /** OUT OF FLOOR */
        //First, we check if out of bound
        if ($currentFloor === null) {
            return INF;
        }

        //we set that this is where we go up from this floor
        $potentialPath[$currentFloorLevel] = $currentFloorPosition;

        /** CLOSE TO STARTING POINT */
        //if currentFloor on the same floor as starting point
        if ($currentFloorLevel === $this->map->getStartingFloor()->getIndexFloor()) {
            $firstBlockTimeOut = 0;
            if($currentFloorPosition < $this->map->getStartingPosition()){
                $firstBlockTimeOut = 3;
            }
            //we return distance from this point to starting point
            return $firstBlockTimeOut + abs($currentFloorPosition - $this->map->getStartingPosition());
        }

        $lowerFloor = $currentFloor->getLowerLevel();

        //if no lower floor => OUT OF BOUND => WRONG PATH
        if ($lowerFloor === null) {
            return INF;
        }

        //lower floor requires an elevator built but none available => WRONG PATH
        if ($availableElevatorsForLowerLevel <= 0 && !$lowerFloor->hasElevators()) {
            return INF;
        }

        $currentFloorBestPositionLength = INF;
        $bestPath = $potentialPath;
        $bestPosition = 0;

        for ($lowerFloorPosition = 1; $lowerFloorPosition <= $this->map->getWidth() - 1; $lowerFloorPosition++) {
            //we copy the potential path to branch it out
            $currentPathPosition = $potentialPath;

            //we check that we have can build or use an elevator from this position at lower level
            if ($availableElevatorsForLowerLevel <= 0 && !$lowerFloor->hasElevatorAtPosition($lowerFloorPosition)) {
                continue;
            }

            //check if lower floor can directly elevate us from this position
            if ($lowerFloor->hasElevatorAtPosition($lowerFloorPosition)) {
                $lowerFloorBestPositionLength = $this->buildPath($currentFloorLevel - 1, $lowerFloorPosition, $currentPathPosition, $availableElevatorsForLowerLevel);
            } else {
                // we need to build a new elevator
                $lowerFloorBestPositionLength = $this->buildPath($currentFloorLevel - 1, $lowerFloorPosition, $currentPathPosition, $availableElevatorsForLowerLevel - 1);
            }

            //if the path from this position is shorter than the best one
            if ($lowerFloorBestPositionLength < $currentFloorBestPositionLength) {
                $bestPosition = $lowerFloorPosition;
                //we update the best length
                $currentFloorBestPositionLength = $lowerFloorBestPositionLength;
                //we update the best path
                $bestPath = $currentPathPosition;
            }
        }

        $potentialPath = $bestPath;

        return $currentFloorBestPositionLength + abs($currentFloorPosition - $bestPosition);
    }

    /**
     * @return \Map
     */
    public function getMap()
    {
        return $this->map;
    }

    public function giveDirective($leadingCloneFloor, $leadingClonePosition, $direction)
    {
        if ($leadingCloneFloor == -1) {
            return Game::__DIRECTIVE_WAIT;
        }

        $optimalPosition = $this->optimalPath[$leadingCloneFloor];

        error_log(var_export("Floor => " . $leadingCloneFloor, true));
        error_log(var_export("Position => " . $optimalPosition, true));

        $leadingClone = new LeadingBot($this->map, $this->map->getFloor($leadingCloneFloor), $leadingClonePosition, $direction);

        $currentFloor = $leadingClone->getFloor();

        if ($currentFloor->needBlockade($leadingClone, $optimalPosition)) {
            $currentFloor->blockPosition($leadingClone->getPosition());

            return Game::__DIRECTIVE_BLOCK;
        }

        if ($leadingClone->getPosition() === $optimalPosition) {
            if (!$currentFloor->hasElevatorAtPosition($optimalPosition)) {
                $currentFloor->addElevator($optimalPosition);

                return Game::__DIRECTIVE_ELEVATOR;
            }
        }

        return Game::__DIRECTIVE_WAIT;
    }

}

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

// $nbFloors: number of floors
// $width: width of the area
// $nbRounds: maximum number of rounds
// $exitFloor: floor on which the exit is found
// $exitPos: position of the exit on its floor
// $nbTotalClones: number of generated clones
// $nbAdditionalElevators: number of additional elevators that you can build
// $nbElevators: number of elevators
fscanf(STDIN, "%d %d %d %d %d %d %d %d", $nbFloors, $width, $nbRounds, $exitFloor, $exitPos, $nbTotalClones, $nbAdditionalElevators, $nbElevators);

$game = new Game($nbFloors, $width, $nbRounds, $exitFloor, $exitPos, $nbTotalClones, $nbAdditionalElevators, $nbElevators);

for ($i = 0; $i < $nbElevators; $i++) {
    // $elevatorFloor: floor on which this elevator is found
    // $elevatorPos: position of the elevator on its floor
    fscanf(STDIN, "%d %d", $elevatorFloor, $elevatorPos);
    $game->getMap()->getFloor($elevatorFloor)->addElevator($elevatorPos);
}

$firstIteration = true;

// game loop
while (true) {
    // $leadingCloneFloor: floor of the leading clone
    // $leadingClonePosition: position of the leading clone on its floor
    // $direction: direction of the leading clone: LEFT or RIGHT
    fscanf(STDIN, "%d %d %s", $leadingCloneFloor, $leadingClonePosition, $direction);

    if ($firstIteration) {
        $game->getMap()->setStartingPoint($leadingCloneFloor, $leadingClonePosition);

        $game->findBestPath();

        $firstIteration = false;
    }

    $directive = $game->giveDirective($leadingCloneFloor, $leadingClonePosition, $direction);

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug (equivalent to var_dump): error_log(var_export($var, true));
    echo("$directive\n");
}
