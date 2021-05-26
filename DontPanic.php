<?php
/**
 * Coding challenge : https://www.codingame.com/ide/puzzle/don't-panic-episode-1
 * Coding challenge : https://www.codingame.com/ide/puzzle/don't-panic-episode-2
 * PHP : 7.2
 *
 * @version 1.4
 * pass 10/10 tests
 * 100% success on submit
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

    /**
     */
    public function switchDirection()
    {
        $this->direction = self::getOppositeDirection($this->direction);
    }

    /**
     * @param  string  $direction
     *
     * @return string
     */
    public static function getOppositeDirection(string $direction)
    {
        if ($direction === LeadingBot::__DIRECTION_RIGHT) {
            return LeadingBot::__DIRECTION_LEFT;
        }

        return LeadingBot::__DIRECTION_RIGHT;
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
     * @param  int       $currentPosition
     * @param  string    $currentDirection
     * @param  int|null  $targetPosition
     *
     * @return bool
     */
    public function needBlockade(int $currentPosition, string $currentDirection, int $targetPosition = null)
    {
        if ($this->hasBlockade()) {
            return false;
        }

        if ($targetPosition === null) {
            return false;
        }

        if ($currentPosition === $targetPosition) {
            return false;
        }

        //target destination on the right and going left
        if ($currentPosition < $targetPosition && $currentDirection === LeadingBot::__DIRECTION_LEFT) {
            return true;
        }

        //target destination on the left and going right
        if ($currentPosition > $targetPosition && $currentDirection === LeadingBot::__DIRECTION_RIGHT) {
            return true;
        }

        return false;
    }

    /**
     * Find first elevator on the left & right of current position
     *
     * @param  int  $position
     *
     * @return int[]|null[]
     */
    public function closestElevators(int $position)
    {
        $closestRight = null;
        $closestLeft = null;

        foreach ($this->getElevators() as $elevatorPositions) {
            if ($elevatorPositions < $position) {
                //fetch closest LEFT
                if ($closestLeft === null || abs($position - $closestLeft) > abs($elevatorPositions - $position)) {
                    $closestLeft = $elevatorPositions;
                }
            } elseif ($elevatorPositions > $position) {
                //fetch closest RIGHT
                if ($closestRight === null || abs($position - $closestRight) > abs($elevatorPositions - $position)) {
                    $closestRight = $elevatorPositions;
                }
            }
        }

        return ['left' => $closestLeft, 'right' => $closestRight];
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
    /** @var int */
    private $nbClones;
    /** @var int */
    private $nbRounds;

    public function __construct(int $nbFloors, int $width, int $nbRounds, int $exitFloor, int $exitPos, int $nbTotalClones, int $nbAdditionalElevators, int $nbElevators)
    {
        $this->totalAdditional = $nbAdditionalElevators;
        $this->map = new Map($width, $nbFloors, $exitFloor, $exitPos);
        $this->nbRounds = $nbRounds;
        $this->nbClones = $nbTotalClones;
    }

    public function findBestPath()
    {
        $this->optimalPath = [];
        $totalLength = $this->buildPathFromTopToBottom(
            $this->map->getExitFloor()->getIndexFloor(),
            $this->map->getExitPosition(),
            $this->optimalPath,
            $this->totalAdditional,
            $this->nbClones
        );

        error_log(var_export($totalLength, true));
        error_log(var_export($this->optimalPath, true));
        error_log(var_export($this->nbRounds, true));

        if ($totalLength === INF) {
            //throw new LogicException("NO PATH FOUND");
        }
    }

    private const TURN_SAME_POS_AFTER_BLOCK_OR_BUILD = 3;
    private const TURN_TO_CLIMB                      = 1;

    private static $iteration = 0;

    /**
     * finding best path from top to bottom
     *
     * @param  int    $currentFloorLevel
     * @param  int    $currentFloorPositionToTest
     * @param  array  $potentialPathToTake
     * @param  int    $availableElevatorsForLowerLevel
     * @param  int    $savedClones
     *
     * @return int
     */
    private function buildPathFromTopToBottom(int $currentFloorLevel, int $currentFloorPositionToTest, array &$potentialPathToTake, int $availableElevatorsForLowerLevel, int $savedClones)
    {
        //no more clone to save
        if ($savedClones <= 0) {
            return INF;
        }

        $currentFloor = $this->map->getFloor($currentFloorLevel);

        /** OUT OF FLOOR */
        //We check if out of bound
        if ($currentFloor === null) {
            return INF;
        }

        //we set that this is where we go up from this floor
        $potentialPathToTake[$currentFloorLevel] = $currentFloorPositionToTest;

        $isStartingFloor = ($currentFloorLevel === $this->map->getStartingFloor()->getIndexFloor());

        $lowerFloor = $currentFloor->getLowerLevel();

        //if we can keep going down
        if (!$isStartingFloor){
            //if no lower floor => OUT OF BOUND => WRONG PATH
            if($lowerFloor === null) {
                return INF;
            }

            //lower floor requires an elevator built but none available => WRONG PATH
            if ($availableElevatorsForLowerLevel <= 0 && !$lowerFloor->hasElevators()) {
                return INF;
            }
        }

        /** CLOSE TO STARTING POINT */
        //if currentFloor on the same floor as starting point
        if ($isStartingFloor) {
            $cumulTurnBlockingWaitingClimbing = 0;

            $firstFloorDirection = LeadingBot::__DIRECTION_RIGHT;
            if ($this->map->getStartingFloor()->needBlockade($this->map->getStartingPosition(), $firstFloorDirection, $currentFloorPositionToTest)) {
                //block turn
                $cumulTurnBlockingWaitingClimbing += self::TURN_SAME_POS_AFTER_BLOCK_OR_BUILD;
                $firstFloorDirection = LeadingBot::__DIRECTION_LEFT;
            }

            $currentDirection = $firstFloorDirection;
            for ($i = 1, $iMax = count($potentialPathToTake); $i < $iMax; $i++) {
                //one turn to go up
                $cumulTurnBlockingWaitingClimbing += self::TURN_TO_CLIMB;

                $tmpFloor = $this->map->getFloor($i);
                //out of bound
                if ($tmpFloor === null) {
                    return INF;
                }

                if ($tmpFloor->needBlockade($potentialPathToTake[$i - 1], $currentDirection, $potentialPathToTake[$i])) {
                    //block/built turn
                    $cumulTurnBlockingWaitingClimbing += self::TURN_SAME_POS_AFTER_BLOCK_OR_BUILD;
                    $currentDirection = LeadingBot::getOppositeDirection($currentDirection);
                    $savedClones--;
                }
            }

            //no more clones to save after completing potential path
            if ($savedClones <= 0) {
                return INF;
            }

            //one turn for last floor
            $cumulTurnBlockingWaitingClimbing += self::TURN_TO_CLIMB;

            //we return the cumul of all the waiting and blocking and climibing + distance from this first floor possibility to starting point
            return $cumulTurnBlockingWaitingClimbing + abs($currentFloorPositionToTest - $this->map->getStartingPosition());
        }


        $lowerFloorBestPositionToClimbLength = INF;
        $bestPotentialPathToTake = $potentialPathToTake;

        /** USING ELEVATOR DIRECTLY UNDER CURRENT FLOOR POSITION */
        if ($lowerFloor->hasElevatorAtPosition($currentFloorPositionToTest)) {
            // we come from lower floor elevator
            return $this->buildPathFromTopToBottom(
                $currentFloorLevel - 1,
                $currentFloorPositionToTest,
                $potentialPathToTake,
                $availableElevatorsForLowerLevel,
                $savedClones
            );
        }

        /** CREATING ELEVATOR DIRECTLY UNDER CURRENT FLOOR POSITION POSSIBILITY */
        if ($availableElevatorsForLowerLevel > 0 && !$lowerFloor->hasElevatorAtPosition($currentFloorPositionToTest)) {
            //we copy the potential path to branch it out
            $currentPathPosition = $potentialPathToTake;
            // we need to build a new elevator
            $lowerFloorBestPathLength = $this->buildPathFromTopToBottom(
                $currentFloorLevel - 1,
                $currentFloorPositionToTest,
                $currentPathPosition,
                $availableElevatorsForLowerLevel - 1,
                $savedClones - 1
            );

            $lowerFloorBestPathLength += self::TURN_SAME_POS_AFTER_BLOCK_OR_BUILD;

            //if the path from this position is shorter than the best one
            if ($lowerFloorBestPathLength < $lowerFloorBestPositionToClimbLength) {
                //we update the best length
                $lowerFloorBestPositionToClimbLength = $lowerFloorBestPathLength;
                //we update the best path
                $bestPotentialPathToTake = $currentPathPosition;
            }
        }

        /** FETCHING WHERE ELEVATOR CANNOT BE USED/BUILT ON LOWER FLOOR */
        $closestCurrentFloorElevators = $currentFloor->closestElevators($currentFloorPositionToTest);
        $minLowerPositionNotUsable = $closestCurrentFloorElevators['left'];
        $maxLowerPositionNotUsable = $closestCurrentFloorElevators['right'];

        foreach ($lowerFloor->getElevators() as $elevatorPosition) {

            if ($minLowerPositionNotUsable !== null && $elevatorPosition <= $minLowerPositionNotUsable) {
                continue;
            }

            if ($maxLowerPositionNotUsable !== null && $elevatorPosition >= $maxLowerPositionNotUsable) {
                continue;
            }

            //we copy the potential path to branch it out
            $currentPathPosition = $potentialPathToTake;
            $lowerFloorBestPathLength = $this->buildPathFromTopToBottom(
                $currentFloorLevel - 1,
                $elevatorPosition,
                $currentPathPosition,
                $availableElevatorsForLowerLevel,
                $savedClones
            );

            $lowerFloorBestPathLength += abs($currentFloorPositionToTest - $elevatorPosition);

            //error_log(var_export(implode('=>',$currentPathPosition),true));
            //error_log(var_export("Length => " . $lowerFloorBestPathLength,true));

            //if the path from this position is shorter than the best one
            if ($lowerFloorBestPathLength < $lowerFloorBestPositionToClimbLength) {
                //we update the best length
                $lowerFloorBestPositionToClimbLength = $lowerFloorBestPathLength;
                //we update the best path
                $bestPotentialPathToTake = $currentPathPosition;
            }
        }

        $potentialPathToTake = $bestPotentialPathToTake;

        $finalLength = $lowerFloorBestPositionToClimbLength;

        if ($finalLength >= $this->nbRounds) {
            return INF;
        }

        return $finalLength;
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

        $leadingClone = new LeadingBot($this->map, $this->map->getFloor($leadingCloneFloor), $leadingClonePosition, $direction);

        $currentFloor = $leadingClone->getFloor();

        if ($currentFloor->needBlockade($leadingClone->getPosition(), $leadingClone->getDirection(), $optimalPosition)) {
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

    //error_log(var_export($leadingClonePosition, true));
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
