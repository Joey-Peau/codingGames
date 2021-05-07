<?php

const __WAIT = 'WAIT';
const __BLOCK = 'BLOCK';
const __ELEVATOR = 'ELEVATOR';

class Map
{
    /** @var Floor[] */
    private $floors = [];
    /** @var int */
    private $width;
    /** @var Floor */
    private $exit;

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    public function __construct(int $width, int $nbFloors, int $exitFloor, int $exitPosition)
    {
        $this->width = $width;
        for ($nb = 0; $nb < $nbFloors; $nb++) {
            $this->floors[$nb] = new Floor($this);
        }

        $this->exit = $this->floors[$exitFloor];
        $this->exit->addElevator($exitPosition);
    }

    /**
     * @param  int  $index
     *
     * @return Floor
     */
    public function getFloor(int $index)
    {
        return $this->floors[$index];
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
        return $this->exit->getElevators()[0];
    }
}

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
     * @return int|null
     */
    public function getNextPosition()
    {
        error_log(var_export($this->currentFloor->getReference(), true));
        error_log(var_export($this->map->getExitFloor()->getReference(), true));
        //if the leading clone is on exit floor
        if ($this->currentFloor === $this->map->getExitFloor()) {
            //position to go is to exit direction
            return $this->map->getExitPosition();
        }

        return $this->currentFloor->findClosestElevator($this->currentPosition);
    }
}

class Floor
{
    /** @var int */
    private static $count = 0;
    /** @var int  */
    private $reference;
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
        $this->reference = ++self::$count;
    }

    /**
     * @return int
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param  int  $position
     */
    public function addElevator(int $position)
    {
        $this->elevatorPositions[] = $position;
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
     * @param  int  $currentPosition
     *
     * @return int|null
     */
    public function findClosestElevator(int $currentPosition)
    {
        if (!$this->hasElevators()) {
            return null;
        }

        $minPosition = INF;
        $minDistance = INF;

        foreach ($this->elevatorPositions as $elevatorPosition) {
            $currentDistance = abs($elevatorPosition - $currentPosition);
            if ($currentDistance < $minDistance) {
                $minDistance = $currentDistance;
                $minPosition = $elevatorPosition;
            }
        }

        return $minPosition;
    }

    /**
     * @param  \LeadingBot  $bot
     *
     * @return bool
     */
    public function needBlockade(LeadingBot $bot)
    {
        if ($this->hasBlockade()) {
            return false;
        }

        $nextPosition = $bot->getNextPosition();

        if ($nextPosition === null) {
            return false;
        }

        if ($bot->getPosition() === $nextPosition) {
            return false;
        }

        //bot on the left and going left
        if ($bot->getPosition() < $nextPosition && $bot->getDirection() === LeadingBot::__DIRECTION_LEFT) {
            return true;
        }

        //bot on the right and going right
        if ($bot->getPosition() > $nextPosition && $bot->getDirection() === LeadingBot::__DIRECTION_RIGHT) {
            return true;
        }

        return false;
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

$arrayElevator = array();
$arrayBlocked = array();
error_log(var_export($nbAdditionalElevators, true));

$map = new Map($width, $nbFloors, $exitFloor, $exitPos);

for ($i = 0; $i < $nbElevators; $i++) {
    // $elevatorFloor: floor on which this elevator is found
    // $elevatorPos: position of the elevator on its floor
    fscanf(STDIN, "%d %d", $elevatorFloor, $elevatorPos);
    $map->getFloor($elevatorFloor)->addElevator($elevatorPos);
}

// game loop
while (true) {
    $directive = __WAIT;
    //error_log(var_export($map, true));

    // $leadingCloneFloor: floor of the leading clone
    // $leadingClonePosition: position of the leading clone on its floor
    // $direction: direction of the leading clone: LEFT or RIGHT
    fscanf(STDIN, "%d %d %s", $leadingCloneFloor, $leadingClonePosition, $direction);

    if ($leadingCloneFloor == -1) {
        echo(__WAIT . "\n");
        continue;
    }

    $leadingClone = new LeadingBot($map, $map->getFloor($leadingCloneFloor), $leadingClonePosition, $direction);

    $posToGo = $leadingClone->getNextPosition();

    $currentFloor = $leadingClone->getFloor();

    if ($posToGo === null) {
        $currentFloor->addElevator($leadingClone->getPosition());
        echo(__ELEVATOR . "\n");
        continue;
    }

    if ($currentFloor->needBlockade($leadingClone)) {
        $directive = __BLOCK;
        $currentFloor->blockPosition($leadingClone->getPosition());
    }

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug (equivalent to var_dump): error_log(var_export($var, true));

    echo("$directive\n"); // action: WAIT or BLOCK
}
