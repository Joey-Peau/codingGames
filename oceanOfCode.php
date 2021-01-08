<?php
/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

fscanf(STDIN, "%d %d %d", $width, $height, $myId);

$board = new Board($height, $width);

for ($y = 0; $y < $height; $y++) {
    $line       = stream_get_line(STDIN, $width + 1, "\n");
    $lengthLine = strlen($line);
    for ($x = 0; $x < $lengthLine; $x++) {
        $board->getCell($x, $y)->setIsLand($line[$x] != ".");
    }
}

/**
 * Board game
 */
class Board
{

    private $width;
    private $height;

    private $cellsCoordinates = [];
    private $sectors          = [];
    private $rawCells         = [];

    public function __construct(int $rows, int $width)
    {
        $this->width  = $width;
        $this->height = $rows;
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $sector                         = $this->getSectorIndexXY($x, $y);
                $newCell                        = new Cell($this, $x, $y, $sector);
                $this->cellsCoordinates[$y][$x] = $newCell;
                $this->sectors[$sector][]       = $newCell;
                $this->rawCells[]               = $newCell;
            }
        }
    }

    /**
     * List of all the cells on the board
     *
     * @return Cell[]
     */
    public function getCells(): array
    {
        return $this->rawCells;
    }

    /**
     * get sector index by X and Y
     *
     * @param  int  $x
     * @param  int  $y
     *
     * @return int
     */
    public function getSectorIndexXY(int $x, int $y)
    {
        $ySector = floor($y * 3 / $this->height);
        $xSector = floor($x * 3 / $this->width);

        return (3 * $ySector) + ($xSector + 1);
    }

    /**
     * Return board cell at coordinate X & Y
     *
     * @param  int  $x
     * @param  int  $y
     *
     * @return Cell|null
     */
    public function getCell(int $x, int $y): ?Cell
    {
        return $this->cellsCoordinates[$y][$x] ?? null;
    }

    /**
     * Get all surronding Cells
     *
     * @param  Cell         $cell
     * @param  bool         $onlyMovable    only NEWS position (no diagonal)
     * @param  string|null  $forcePosition  force a cardinal position
     *
     * @return Cell[]|null[]
     */
    public function getNeightbors(Cell $cell, bool $onlyMovable = true, string $forcePosition = null): array
    {
        $yCoordinate = ["N", "", "S"];
        $xCoordinate = ["W", "", "E"];

        $coordinates = [
            "NW" => null,
            "N"  => null,
            "NE" => null,
            "E"  => null,
            "SE" => null,
            "S"  => null,
            "SW" => null,
            "W"  => null,
        ];

        $xCell = $cell->getX();
        $yCell = $cell->getY();

        $forceX = null;
        $forceY = null;

        //if we force a cardinal position
        if ($forcePosition !== null) {
            //finding N-S position
            foreach ($yCoordinate as $index => $value) {
                if ($value !== "" && strpos($forcePosition, $value) > 0) {
                    $forceY = $index;
                    break;
                }
            }
            //finding W-E position
            foreach ($xCoordinate as $index => $value) {
                if ($value !== "" && strpos($forcePosition, $value) > 0) {
                    $forceX = $index;
                    break;
                }
            }
        }

        $yIndex = -1;
        //from north to south
        for ($y = $yCell - 1; $y <= $yCell + 1; $y++) {
            $yIndex++;
            //out of bound
            if ($y < 0 || $y > $this->height - 1) {
                continue;
            }

            if ($forceY !== null) {
                if ($yIndex !== $forceY) {
                    //forced N-S position not found
                    continue;
                }
            }

            $xIndex = -1;
            //from west to east
            for ($x = $xCell - 1; $x <= $xCell + 1; $x++) {
                $xIndex++;
                //out of bound
                if ($x < 0 || $x > $this->width - 1) {
                    continue;
                }

                if ($forceX !== null) {
                    if ($xIndex !== $forceX) {
                        //forced W-E position not found
                        continue;
                    }
                }

                //current cell
                if ($x == $xCell && $y == $yCell) {
                    continue;
                }

                $canAdd = true;

                if ($onlyMovable) {
                    $canAdd = false;

                    if ($yCoordinate[$yIndex] == "" && strpos("NEWS", $xCoordinate[$xIndex]) > -1) {
                        $canAdd = true;
                    }

                    if ($xCoordinate[$xIndex] == "" && strpos("NEWS", $yCoordinate[$yIndex]) > -1) {
                        $canAdd = true;
                    }
                }

                if ($canAdd) {
                    $coordinates[$yCoordinate[$yIndex].$xCoordinate[$xIndex]] = $this->getCell($x, $y);
                }
            }
        }

        return $coordinates;
    }

    /**
     * Print the board
     *
     * @return string
     */
    public function __toString()
    {
        $string = "";
        foreach ($this->cellsCoordinates as $row => $column) {
            /** @var Cell $cell */
            foreach ($column as $cell) {
                $string .= $cell->isLand() ? "1" : "0";
            }
            $string .= "\n";
        }

        return $string;
    }

    /**
     * @return array
     */
    public function getSectors(): array
    {
        return $this->sectors;
    }

}

/**
 * Cell on the board game
 */
class Cell
{
    private $x;
    private $y;
    private $isLand;

    private $sector;

    public function __construct(Board $board, int $x, int $y, int $sector, ?bool $isLand = false)
    {
        $this->board  = $board;
        $this->x      = $x;
        $this->y      = $y;
        $this->sector = $sector;
        $this->isLand = $isLand;
    }

    /**
     * X (column) coordinate
     *
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * Y (row) coordinate
     *
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * Sector the cell belongs to
     *
     * @return int
     */
    public function getSector(): int
    {
        return $this->sector;
    }

    /**
     * set the land status of the cell
     *
     * @param  bool  $isLand
     *
     * @return $this
     */
    public function setIsLand(bool $isLand): self
    {
        $this->isLand = $isLand;

        return $this;
    }

    /**
     * Is this cell a land
     *
     * @return bool
     */
    public function isLand(): bool
    {
        return $this->isLand;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{".$this->sector.":".$this->x.",".$this->y.",".(int) $this->isLand."}";
    }

    public function getNorthCell(): ?Cell
    {
        return $this->board->getNeightbors($this, true, "N")["N"];
    }

    public function getEastCell(): ?Cell
    {
        return $this->board->getNeightbors($this, true, "E")["E"];
    }

    public function getWestCell(): ?Cell
    {
        return $this->board->getNeightbors($this, true, "W")["W"];
    }

    public function getSouthCell(): ?Cell
    {
        return $this->board->getNeightbors($this, true, "S")["S"];
    }
}

/**
 * Any submarine (might be the player, the enemy or any other possibilities)
 */
class SubMarine
{

    public const __MAX_SONAR_TIME   = 4;
    public const __MAX_SILENCE_TIME = 6;
    public const __MAX_TORPEDO_TIME = 3;
    public const __MAX_MINE_TIME    = 0;
    public const __MAX_LIFE         = 6;
    private $cellPath        = [];
    private $currentCell;
    private $previousCell;
    private $life            = SubMarine::__MAX_LIFE;
    private $torpedoCooldown = 0;
    private $sonarCooldown   = 0;
    private $silenceCooldown = 0;
    private $mineCooldown    = 0;

    public function __construct(Cell $cell)
    {
        $this->currentCell = $cell;
    }

    /**
     * find if the torpido destination cell has hit the sub.
     * Removes life accordingly
     *
     * @param  Cell  $cell
     *
     * @return bool
     */
    public function hasTorpidoHit(Cell $cell): bool
    {
        if ($this->currentCell === $cell) {
            $this->life -= 2;

            return true;
        }

        $north = $this->currentCell->getNorthCell();
        if ($north && !$north->isLand()) {
            if ($north === $cell) {
                $this->life--;

                return true;
            }

            $northEast = $north->getEastCell();
            if ($northEast && !$northEast->isLand()) {
                if ($northEast === $cell) {
                    $this->life--;

                    return true;
                }
            }

            $northWest = $north->getWestCell();
            if ($northWest && !$northWest->isLand()) {
                if ($northWest === $cell) {
                    $this->life--;

                    return true;
                }
            }
        }

        $east = $this->currentCell->getEastCell();
        if ($east && !$east->isLand()) {
            if ($east === $cell) {
                $this->life--;

                return true;
            }
        }

        $west = $this->currentCell->getWestCell();
        if ($west && !$west->isLand()) {
            if ($west === $cell) {
                $this->life--;

                return true;
            }
        }

        $south = $this->currentCell->getSouthCell();
        if ($south && !$south->isLand()) {
            if ($south === $cell) {
                $this->life--;

                return true;
            }

            $southEast = $south->getEastCell();
            if ($southEast && !$southEast->isLand()) {
                if ($southEast === $cell) {
                    $this->life--;

                    return true;
                }
            }

            $southWest = $south->getWestCell();
            if ($southWest && !$southWest->isLand()) {
                if ($southWest === $cell) {
                    $this->life--;

                    return true;
                }
            }
        }

        return false;
    }

    public function potentialCharge(): void
    {
        $this->torpedoCooldown--;
        $this->sonarCooldown--;
        $this->silenceCooldown--;
        $this->mineCooldown--;
    }

    /**
     * @param  int  $life
     *
     * @return SubMarine
     */
    public function setLife(int $life): SubMarine
    {
        $this->life = $life;

        return $this;
    }

    /**
     * @return bool
     */
    public function canUseMine(): bool
    {
        return $this->mineCooldown <= 0;
    }

    public function useMine(): void
    {
        $this->mineCooldown = SubMarine::__MAX_MINE_TIME;
    }

    /**
     * @return int
     */
    public function getMineCooldown(): int
    {
        return $this->mineCooldown;
    }

    /**
     * @param  int  $mineCooldown
     *
     * @return SubMarine
     */
    public function setMineCooldown(int $mineCooldown): SubMarine
    {
        $this->mineCooldown = $mineCooldown;

        return $this;
    }

    /**
     * @return bool
     */
    public function canUseSonar(): bool
    {
        return $this->sonarCooldown <= 0;
    }

    public function useSonar(): void
    {
        $this->sonarCooldown = SubMarine::__MAX_SONAR_TIME;
    }

    /**
     * @return int
     */
    public function getSonarCooldown(): int
    {
        return $this->sonarCooldown;
    }

    /**
     * @param  int  $sonarCooldown
     *
     * @return SubMarine
     */
    public function setSonarCooldown(int $sonarCooldown): SubMarine
    {
        $this->sonarCooldown = $sonarCooldown;

        return $this;
    }

    /**
     * @return bool
     */
    public function canUseSilence(): bool
    {
        return $this->silenceCooldown <= 0;
    }

    public function useSilence(): void
    {
        $this->silenceCooldown = SubMarine::__MAX_SILENCE_TIME;
    }

    /**
     * @return int
     */
    public function getSilenceCooldown(): int
    {
        return $this->silenceCooldown;
    }

    /**
     * @param  int  $silenceCooldown
     *
     * @return SubMarine
     */
    public function setSilenceCooldown(int $silenceCooldown): SubMarine
    {
        $this->silenceCooldown = $silenceCooldown;

        return $this;
    }

    /**
     * @return bool
     */
    public function canUseTorpedo(): bool
    {
        return $this->torpedoCooldown <= 0;
    }

    public function useTorpedo(): void
    {
        $this->torpedoCooldown = SubMarine::__MAX_TORPEDO_TIME;
    }

    /**
     * @return int
     */
    public function getTorpedoCooldown(): int
    {
        return $this->torpedoCooldown;
    }

    /**
     * @param  int  $torpedoCooldown
     *
     * @return SubMarine
     */
    public function setTorpedoCooldown(int $torpedoCooldown): SubMarine
    {
        $this->torpedoCooldown = $torpedoCooldown;

        return $this;
    }

    /**
     * Return the previous cell the sub was on
     *
     * @return Cell
     */
    public function getPreviousCell(): Cell
    {
        return $this->previousCell;
    }

    public function surface(): void
    {
        $this->cellPath   = [];
        $this->cellPath[] = $this->currentCell;
        $this->life--;
    }

    /**
     * Find North cell from sub marine position
     *
     * @return Cell|null
     */
    public function getNorth(): ?Cell
    {
        return $this->currentCell->getNorthCell();
    }

    /**
     * Find East cell from sub marine position
     *
     * @return Cell|null
     */
    public function getEast(): ?Cell
    {
        return $this->currentCell->getEastCell();
    }

    /**
     * Find West cell from sub marine position
     *
     * @return Cell|null
     */
    public function getWest(): ?Cell
    {
        return $this->currentCell->getWestCell();
    }

    /**
     * Find South cell from sub marine position
     *
     * @return Cell|null
     */
    public function getSouth(): ?Cell
    {
        return $this->currentCell->getSouthCell();
    }

    /**
     * The current cell the sub is on
     *
     * @return Cell | null
     */
    public function getCurrentCell(): ?Cell
    {
        return $this->currentCell;
    }

    /**
     * Is the sub still operationnal
     *
     * @return bool
     */
    public function isAlive(): bool
    {
        return $this->life > 0 && !$this->isCrashed();
    }

    /**
     * print current cell
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->currentCell) {
            return "CRASHED";
        }

        return $this->currentCell->__toString();
    }

    /**
     * Is the cell a movable one
     *
     * @param  Cell|null  $cell
     *
     * @return bool
     */
    public function isMovableCell(?Cell $cell): bool
    {
        if ($cell === null) {
            return false;
        }

        if ($cell->isLand()) {
            return false;
        }

        if (in_array($cell, $this->cellPath)) {
            return false;
        }

        return true;
    }

    /**
     * Move the submarine in any direction for a certain amount of cells
     *
     * @param  string    $direction
     * @param  int|null  $nbMove
     */
    public function move(string $direction, int $nbMove = 1): void
    {
        $this->previousCell = $this->currentCell;

        $this->moveMultiple($direction, $nbMove);
    }

    /**
     * max number of steps allowed in straight line
     *
     * @param        $coordinate
     *
     * @return int
     */
    public function nbStepsInStraightLine($coordinate): int
    {
        $nbSteps     = 0;
        $currentCell = $this->currentCell;
        while (true) {
            $newCell = null;
            switch ($coordinate) {
                case "N":
                    $newCell = $currentCell->getNorthCell();
                    break;
                case "E":
                    $newCell = $currentCell->getEastCell();
                    break;
                case "W":
                    $newCell = $currentCell->getWestCell();
                    break;
                case "S":
                    $newCell = $currentCell->getSouthCell();
                    break;
            }

            if (!$newCell) {
                break;
            }

            if (!$this->isMovableCell($newCell)) {
                break;
            }
            $currentCell = $newCell;
            $nbSteps++;
        }

        return $nbSteps;
    }

    /**
     * is the sub crashed
     *
     * @return bool
     */
    private function isCrashed(): bool
    {
        return $this->currentCell == null || $this->currentCell->isLand();
    }

    /**
     * Allow to move multiple cells at once
     * stops as soon as sub is crashed
     *
     * @param  string    $direction  N,E,W,S...
     * @param  int|null  $nbMove
     */
    private function moveMultiple(string $direction, int $nbMove = 1): void
    {
        if (!$this->isAlive()) {
            return;
        }

        if ($nbMove == 0) {
            return;
        }

        $currentCell = $this->currentCell;

        switch ($direction) {
            case "N":
                $newCell = $currentCell->getNorthCell();
                break;
            case "E":
                $newCell = $currentCell->getEastCell();
                break;
            case "W":
                $newCell = $currentCell->getWestCell();
                break;
            case "S":
                $newCell = $currentCell->getSouthCell();
                break;
            default:
                $newCell = $currentCell;
                break;
        }

        if ($newCell !== $currentCell) {
            if (!$this->isMovableCell($newCell)) {
                $this->life = -1;
            }
            $this->cellPath[] = $this->currentCell;
        }

        $this->currentCell = $newCell;

        if ($nbMove > 0) {
            $this->moveMultiple($direction, $nbMove - 1);
        }
    }

}


class SubsPossibility implements ArrayAccess, Iterator, Countable
{

    /** @var SubMarine[] */
    private $subs         = array();
    private $currentIndex = 0;

    /**
     * Deep Clone the list of possibilities
     */
    public function __clone()
    {
        $newList = [];
        foreach ($this->subs as $sub) {
            $newList[] = clone $sub;
        }

        $this->subs = $newList;
    }

    /**
     * Count the number of possibilities
     *
     * @return int
     */
    public function count()
    {
        return count($this->subs);
    }

    public function first(): ?SubMarine
    {
        return $this->subs[0] ?? null;
    }

    public function refreshList(): void
    {
        foreach ($this->subs as $index => $currentSub) {
            if ($currentSub == null) {
                unset($this->subs[$index]);
                continue;
            }

            if (!$currentSub->isAlive()) {
                unset($this->subs[$index]);
                continue;
            }
        }

        $this->subs         = array_values($this->subs);
        $this->currentIndex = 0;
    }

    /**
     * Get the current possibility
     *
     * @return mixed|SubMarine
     */
    public function current()
    {
        return $this->subs[$this->currentIndex];
    }

    /**
     * My list key
     *
     * @return string
     */
    public function key()
    {
        $currentCell = $this->current()->getCurrentCell();

        if (!$currentCell) {
            return "CRASHED";
        }

        return $currentCell->__toString();
    }

    /**
     * Next element in array
     */
    public function next()
    {
        $this->currentIndex++;
    }

    /**
     * reset the array
     */
    public function rewind()
    {
        $this->refreshList();
    }

    /**
     * is last element of the array
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->subs[$this->currentIndex]);
    }

    /**
     * does the key exists
     *
     * @param $xy
     *
     * @return bool
     * @throws LogicException
     */
    public function offsetExists($xy)
    {
        if (!$xy instanceof SubMarine) {
            throw new LogicException("Only sub object key accepted");
        }

        $index = $this->findIndexBySub($xy);

        return $index !== false;
    }

    /**
     * get the array by key
     *
     * @param $xy
     *
     * @return mixed|SubMarine|null
     * @throws LogicException
     */
    public function offsetGet($xy)
    {
        if (!$xy instanceof SubMarine) {
            throw new LogicException("Only sub object key accepted");
        }

        $index = $this->findIndexBySub($xy);

        if ($index === false) {
            return ($this->subs[$index]);
        }

        return null;
    }

    /**
     * set the Sub
     *
     * @param  null       $key
     * @param  SubMarine  $sub
     *
     * @throws LogicException
     */
    public function offsetSet($key, $sub)
    {
        if ($key !== null) {
            throw new LogicException("No key to set index");
        }

        if (!$sub instanceof SubMarine) {
            throw new LogicException("No Sub to set");
        }

        if (!$sub->isAlive()) {
            return;
        }

        $index = $this->findIndexBySub($sub);

        if ($index === false) {
            $this->subs[] = $sub;
        }
    }

    /**
     * unset the sub from possibilities
     *
     * @param $subMarine
     *
     * @throws LogicException
     */
    public function offsetUnset($subMarine)
    {
        if (!$subMarine instanceof SubMarine) {
            throw new LogicException("Only sub object key accepted");
        }

        if (!$subMarine->isAlive()) {
            return;
        }

        $index = $this->findIndexBySub($subMarine);

        if ($index !== false) {
            unset($this->subs[$index]);
        }
    }

    /**
     * Find if sub exists in possibilities array
     *
     * @param  SubMarine|null  $subMarine
     *
     * @return false|int|string
     */
    private function findIndexBySub(?SubMarine $subMarine)
    {
        if (!$subMarine) {
            return false;
        }

        if (!$subMarine->isAlive()) {
            return false;
        }

        //return array_search($subMarine, $this->subs);


        /** @var Cell $subCell */
        $subCell = $subMarine->getCurrentCell();

        return array_search($subCell->__toString(),
            array_map(
                static function (SubMarine $possibility) {
                    if (!$possibility->isAlive()) {
                        return "DEAD";
                    }
                    /** @var Cell $possibleCell */
                    $possibleCell = $possibility->getCurrentCell();

                    return $possibleCell->__toString();
                }, $this->subs
            )
        );
    }
}


class Player
{
    /** @var Board */
    private $board;
    /** @var SubsPossibility | SubMarine[] */
    private $possibleEnemies;
    /** @var SubsPossibility | SubMarine[] */
    private $possibleMyPlace;
    /** @var SubMarine */
    private $mySubMarine;
    /** @var SubMarine */
    private $enemySubmarine;
    /** @var int */
    private $tmpSonarSector = 0;
    /** @var bool */
    private $internalHasMoved = false;
    /** @var bool */
    private $myActions;
    /** @var bool */
    private $playingFirst;

    public function __construct(Board $board, ?SubMarine $mySub, bool $playingFirst)
    {
        $this->board        = $board;
        $this->mySubMarine  = $mySub;
        $this->playingFirst = $playingFirst;

        //FIXME algo finding best cell to start from

        $x = 7;
        $y = 7;

        do {
            $startingCell = $this->board->getCell($x--, $y--);
        } while ($startingCell == null || $startingCell->isLand());

        $this->possibleEnemies = new SubsPossibility();
        $this->possibleMyPlace = new SubsPossibility();


        if ($this->mySubMarine == null) {
            $this->mySubMarine = new SubMarine($startingCell);
        }

        $cells = $board->getCells();

        foreach ($cells as $cell) {
            if (!$cell->isLand()) {
                $this->possibleEnemies[] = new SubMarine($cell);
                $this->possibleMyPlace[] = new SubMarine($cell);
            }
        }
    }

    public function getPlayingFirst(): bool
    {
        return $this->playingFirst;
    }

    public function getMySub(): SubMarine
    {
        return $this->mySubMarine;
    }

    /**
     * use enemy action to reduce search area
     *
     * @param  string  $actionsString
     */
    public function translateEnemyActions(string $actionsString): void
    {
        if ($actionsString == "") {
            return;
        }
        if ($actionsString == "NA") {
            return;
        }

        $actions = explode("|", $actionsString);

        $this->myActions = false;

        error_log("Possible enemy position before actions ".count($this->possibleEnemies));

        if (count($this->possibleEnemies) < 10) {
            foreach ($this->possibleEnemies as $possibleEnemy) {
                error_log($possibleEnemy);
            }
        }

        $this->guessActions($this->possibleEnemies, $actions);

        if (count($this->possibleEnemies) == 1) {
            $this->enemySubmarine = $this->possibleEnemies->first();
        } else {
            $this->enemySubmarine = null;
        }

        error_log("Possible enemy position after actions ".count($this->possibleEnemies));

        if ($this->enemySubmarine !== null) {
            error_log("Enemy guessed position => ".$this->enemySubmarine);
        }

        if (count($this->possibleEnemies) < 10) {
            foreach ($this->possibleEnemies as $possibleEnemy) {
                error_log($possibleEnemy);
            }
        }
    }

    /**
     * Function called when sonar has been called by Me
     *
     * @param  string  $result  [Y]es or [N]o if the enemy sub is in the given area
     */
    public function sonarResult(string $result): void
    {
        error_log($result);
        switch ($result) {
            case "Y":
                $isRealInScannedSector = true;
                break;
            case "N":
                $isRealInScannedSector = false;
                break;
            default:
                return;
        }

        $toRemove = [];

        foreach ($this->possibleEnemies as $possibleEnemy) {
            $scannedCell = $possibleEnemy->getPreviousCell();

            $isPossibleInScannedSector = $scannedCell->getSector() === $this->tmpSonarSector;

            if ($isRealInScannedSector) { // the real enemy sub is in scanned sector
                if (!$isPossibleInScannedSector) { //remove all possibilities that are not
                    $toRemove[] = $possibleEnemy;
                }
            } else { // the real enemy sub is not in scanned sector
                if ($isPossibleInScannedSector) { // remove all subs that are
                    $toRemove[] = $possibleEnemy;
                }
            }
        }

        foreach ($toRemove as $sub) {
            unset($this->possibleEnemies[$sub]);
        }

        $this->tmpSonarSector = null;
    }

    /**
     * Remove possibilities based on the actions made
     *
     * @param  SubsPossibility  $possibilities
     * @param  array            $actions
     */
    public function guessActions(SubsPossibility $possibilities, array $actions): void
    {
        $this->internalHasMoved = false;

        foreach ($actions as $action) {
            if (preg_match("/^SURFACE$/iu", $action)) {
                $this->guessSurfaceMe($possibilities);
            } elseif (preg_match("/^SURFACE (\d)/iu", $action, $matches)) {
                $this->guessSurfaceEnemy($possibilities, $matches[1]);
            } elseif (preg_match("/^MOVE ([NEWS])/iu", $action, $matches)) {
                $this->internalHasMoved = true;
                $this->guessMove($possibilities, $matches[1]);
            } elseif (preg_match("/^SONAR (\d)/iu", $action, $matches)) {
                if (!$this->myActions) {
                    $this->guessSonarMe($possibilities, $matches[1]);
                }
            } elseif (preg_match("/^SILENCE/iu", $action, $matches)) {
                $this->internalHasMoved = true;
                $this->guessSilence($possibilities);
            } elseif (preg_match("/^TORPEDO (\d) (\d)/iu", $action, $matches)) {
                $x = $matches[1];
                $y = $matches[2];

                $torpidoDestinationCell = $this->board->getCell($x, $y);

                if (!$torpidoDestinationCell) {
                    continue;
                }

                $this->guessTorpidoLauchCell($possibilities, $torpidoDestinationCell);

                $refresh = false;
                foreach ($this->possibleEnemies as $sub) {
                    $refresh = $refresh || $sub->hasTorpidoHit($torpidoDestinationCell);
                }
                if ($refresh) {
                    $this->possibleEnemies->refreshList();
                }
                $refresh = false;
                foreach ($this->possibleMyPlace as $sub) {
                    $refresh = $refresh || $sub->hasTorpidoHit($torpidoDestinationCell);
                }
                if ($refresh) {
                    $this->possibleMyPlace->refreshList();
                }
            }
        }
    }

    /**
     * The best next cardinalilty to move
     *
     * @return string|null
     */
    public function bestMove(): ?string
    {
        $this->possibleMyPlace;
        /** @var Cell $currentCell */
        $currentCell = $this->mySubMarine->getCurrentCell();

        $maxIndex       = 0;
        $bestCoordinate = null;

        $north = $currentCell->getNorthCell();
        if ($this->mySubMarine->isMovableCell($north)) {
            $possibleNorth = clone $this->possibleMyPlace;
            $this->guessActions($possibleNorth, array("MOVE N"));
            $nbIndex = count($possibleNorth);
            if ($nbIndex > $maxIndex) {
                $bestCoordinate = "N";
                $maxIndex       = $nbIndex;
            }
            unset($possibleNorth);
        }
        unset($north);

        $east = $currentCell->getEastCell();
        if ($this->mySubMarine->isMovableCell($east)) {
            $possibleEast = clone $this->possibleMyPlace;
            $this->guessActions($possibleEast, array("MOVE E"));
            $nbIndex = count($possibleEast);
            if ($nbIndex > $maxIndex) {
                $bestCoordinate = "E";
                $maxIndex       = $nbIndex;
            }
            unset($possibleEast);
        }
        unset($east);

        $west = $currentCell->getWestCell();
        if ($this->mySubMarine->isMovableCell($west)) {
            $possibleWest = clone $this->possibleMyPlace;
            $this->guessActions($possibleWest, array("MOVE W"));
            $nbIndex = count($possibleWest);
            if ($nbIndex > $maxIndex) {
                $bestCoordinate = "W";
                $maxIndex       = $nbIndex;
            }
            unset($possibleWest);
        }
        unset($west);

        $south = $currentCell->getSouthCell();
        if ($this->mySubMarine->isMovableCell($south)) {
            $possibleSouth = clone $this->possibleMyPlace;
            $this->guessActions($possibleSouth, array("MOVE S"));
            $nbIndex = count($possibleSouth);
            if ($nbIndex > $maxIndex) {
                $bestCoordinate = "S";
            }
            unset($possibleSouth);
        }
        unset($south);

        return $bestCoordinate;
    }

    /**
     * //TODO implement commentary
     *
     * @return string
     * @throws Exception
     */
    public function myNextAction(): string
    {
        $orderCharge = array(
            0                  => "TORPEDO",
            random_int(1, 50)  => "SILENCE",
            random_int(2, 100) => "SONAR",
        );

        $charge = null;

        foreach ($orderCharge as $newCharge) {
            switch ($newCharge) {
                case "TORPEDO":
                    if ($this->mySubMarine->canUseTorpedo()) {
                        continue 2;
                    }
                    $charge = $newCharge;
                    break;
                case "SILENCE":
                    if ($this->mySubMarine->canUseSilence()) {
                        continue 2;
                    }
                    $charge = $newCharge;
                    break;
                case "SONAR":
                    if ($this->mySubMarine->canUseSonar()) {
                        continue 2;
                    }
                    $charge = $newCharge;
                    break;
            }
        }

        if ($charge == null) {
            $charge = "SILENCE";
        }

        $actions = [];
        //use the sonar to reduce enemy search
        if ($this->enemySubmarine == null && $this->mySubMarine->canUseSonar()) {
            $sectorsCount  = $this->getEnemyPossiblityCountBySector();
            $activeSectors = array_filter($sectorsCount, static function ($sectorCount) { return $sectorCount > 0; });
            if (count($activeSectors) > 1) {
                arsort($sectorsCount);
                $sectorSonar          = array_key_first($sectorsCount);
                $actions[]            = "SONAR $sectorSonar";
                $this->tmpSonarSector = $sectorSonar;
            }
        }

        if ($this->enemySubmarine && $this->mySubMarine->canUseTorpedo()) {
            $coordinates = $this->enemyInRangeCoordinates();
            if ($coordinates["isInRange"]) {
                $actions[] = "TORPEDO ".$coordinates["x"]." ".$coordinates["y"];
            }
        }

        if ($this->mySubMarine->canUseSilence()) {
            $maxSteps    = 0;
            $maxCardinal = "N";
            foreach (array("N", "E", "W", "S") as $cardinal) {
                $tmpSteps = $this->mySubMarine->nbStepsInStraightLine($cardinal);
                if ($tmpSteps > $maxSteps) {
                    $maxCardinal = $cardinal;
                    $maxSteps    = $tmpSteps;
                }
            }


            $randomPercent = random_int(0, 100);
            if ($randomPercent < random_int(0, 1000)) {
                $maxSteps = 0;
            }

            $maxSteps = min(4, $maxSteps);

            $this->mySubMarine->move($maxCardinal, $maxSteps);

            $actions[] = "SILENCE $maxCardinal $maxSteps";
        } else {
            $cardinal = $this->bestMove();
            if ($cardinal != null) {
                $this->mySubMarine->move($cardinal);
                $actions[] = "MOVE $cardinal $charge";
            } else {
                $this->mySubMarine->surface();
                $actions[] = "SURFACE";
            }
        }

        error_log("Possible ME position before move ".count($this->possibleMyPlace));

        $this->myActions = true;
        $this->guessActions($this->possibleMyPlace, $actions);

        if (count($this->possibleMyPlace) == 1) {
            error_log("My guessed position => ".$this->possibleMyPlace->first());
        }

        error_log("Possible ME position after move ".count($this->possibleMyPlace));

        return $this->parseReturnString($actions);
    }

    /**
     * Check if the enemy is in ranged
     *
     * @return array
     */
    public function enemyInRangeCoordinates(): array
    {
        $arrayInrange = ["isInRange" => false, "x" => 0, "y" => 0];

        if (!$this->enemySubmarine) {
            return $arrayInrange;
        }

        /** @var Cell $enemyCell */
        $enemyCell = $this->enemySubmarine->getCurrentCell();
        /** @var Cell $mycell */
        $mycell = $this->mySubMarine->getCurrentCell();

        if ($this->isCellInTorpidoRange($mycell, $enemyCell)) {
            $arrayInrange["isInRange"] = true;
            $arrayInrange["x"]         = $enemyCell->getX();
            $arrayInrange["y"]         = $enemyCell->getY();

            return $arrayInrange;
        }

        return $arrayInrange;
    }

    /**
     * //TODO implement commentary
     *
     * @param  SubsPossibility  $possibilities
     * @param  Cell             $destinationCell
     *
     * @fixme bancale
     */
    private function guessTorpidoLauchCell(SubsPossibility $possibilities, Cell $destinationCell): void
    {
        $notOk = [];

        /** @var SubMarine $possibleSub */
        foreach ($possibilities as $possibleSub) {
            $possibleSub->useTorpedo();
            $currentCell = $possibleSub->getCurrentCell();
            if ($this->internalHasMoved) {
                $currentCell = $possibleSub->getPreviousCell();
            }

            if (!$this->isCellInTorpidoRange($currentCell, $destinationCell)) {
                $notOk[] = $possibleSub;
            }
        }

        foreach ($notOk as $item) {
            unset($possibilities[$item]);
        }
    }

    private function guessSilence(SubsPossibility $possibilities): void
    {
        $coordinate = ["N", "E", "W", "S"];
        /** @var SubMarine[] $newSubs */
        $newSubs = [];

        /** @var SubMarine $possibleSub */
        foreach ($possibilities as $possibleSub) {
            $possibleSub->useSilence();
            foreach ($coordinate as $direction) {
                for ($j = 1; $j <= 4; $j++) {
                    $potentialSub = clone $possibleSub;
                    $potentialSub->move($direction, $j);
                    //not crashed Sub
                    if ($potentialSub->isAlive()) {
                        $newSubs[] = $potentialSub;
                    }
                }
            }
        }

        foreach ($newSubs as $newSub) {
            $possibilities[] = $newSub;
        }
    }

    private function guessSonarMe(SubsPossibility $possibilities, int $sector): void
    {
        $previousCell     = $this->mySubMarine->getPreviousCell();
        $inSectorResponse = $previousCell->getSector() === $sector;

        $this->guessSonar($possibilities, $sector, $inSectorResponse);
    }

    private function guessSonar(SubsPossibility $possibilities, int $sector, bool $inSectorResponse): void
    {
        $notOk = [];

        /** @var SubMarine $possibleMe */
        foreach ($possibilities as $possibleSub) {
            if ($this->internalHasMoved) {
                $previousCell = $possibleSub->getPreviousCell();
            } else {
                $previousCell = $possibleSub->getCurrentCell();
            }
            if (!$previousCell) {
                continue;
            }

            $isPossibleMeInScannedSector = $previousCell->getSector() === $sector;
            if ($inSectorResponse) { // The sub is in scanned sector
                if (!$isPossibleMeInScannedSector) { //remove all possible subs that are not
                    $notOk[] = $possibleSub;
                }
            } else { // The sub is not in scanned sector
                if ($isPossibleMeInScannedSector) { // remove all possible subs that are
                    $notOk[] = $possibleSub;
                }
            }
        }

        foreach ($notOk as $value) {
            unset($possibilities[$value]);
        }
    }

    /**
     * Remove possibilities based on the direction the sub has taken
     *
     * @param  SubsPossibility  $possibilities
     * @param                   $direction
     */
    private function guessMove(SubsPossibility $possibilities, $direction): void
    {
        $toReload = false;
        /** @var SubMarine $possibleSub */
        foreach ($possibilities as $possibleSub) {
            $possibleSub->potentialCharge();
            $possibleSub->move($direction);
            if (!$possibleSub->isAlive()) {
                $toReload = true;
            }
        }

        if ($toReload) {
            $possibilities->refreshList();
        }
    }

    /**
     * Remove enemy possible positions based on the sector it has surfaced
     *
     * @param  SubsPossibility  $possibilities
     * @param                   $sector
     */
    private function guessSurfaceEnemy(SubsPossibility $possibilities, $sector): void
    {
        $this->guessSurface($possibilities, $sector);
    }

    /**
     * Remove possible positions where MY SUB can be based on the sector I used "surface"
     *
     * @param  SubsPossibility  $possibilities
     */
    private function guessSurfaceMe(SubsPossibility $possibilities): void
    {
        /** @var Cell $currentCell */
        $currentCell = $this->mySubMarine->getCurrentCell();
        $mySector    = $currentCell->getSector();
        $this->guessSurface($possibilities, $mySector);
    }

    /**
     * Remove possible positions of all possibles subs depending on the sector it has been spotted in
     *
     * @param  SubsPossibility  $possibilities
     * @param                   $sector
     */
    private function guessSurface(SubsPossibility $possibilities, $sector): void
    {
        $notOk = [];
        /** @var SubMarine $possibleSub */
        foreach ($possibilities as $possibleSub) {
            $possibleSub->surface();
            if (!$possibleSub->isAlive()) {
                $notOk[] = $possibleSub;
                continue;
            }
            /** @var Cell $possibleCell */
            $possibleCell = $possibleSub->getCurrentCell();
            if ($possibleCell->getSector() != $sector) {
                $notOk[] = $possibleSub;
            }
        }

        foreach ($notOk as $value) {
            unset($possibilities[$value]);
        }
    }

    /**
     * get the number of enemy possibilities by sector
     *
     * @return array
     */
    private function getEnemyPossiblityCountBySector(): array
    {
        $sectors     = $this->board->getSectors();
        $sectorCount = [];
        foreach ($sectors as $index => $sector) {
            $sectorCount[$index] = 0;
        }

        foreach ($this->possibleEnemies as $possibleEnemy) {
            if (!$possibleEnemy->isAlive()) {
                continue;
            }
            /** @var Cell $currentCell */
            $currentCell = $possibleEnemy->getCurrentCell();
            $sectorCount[$currentCell->getSector()]++;
        }

        return $sectorCount;
    }

    private function parseReturnString(array $actions): string
    {
        return implode("|", $actions)."\n";
    }

    /**
     * Find if the destination cell is in another cell torbido range
     *
     * @param  Cell  $sourceCell
     * @param  Cell  $destinationCell  the cell the torpido Hit
     *
     * @return bool
     */
    private function isCellInTorpidoRange(Cell $sourceCell, Cell $destinationCell): bool
    {
        return $this->findInTorpidoRange($sourceCell, $destinationCell);
    }

    /**
     * Recursive function to find if stepCell is in range from torpido Destination cell
     *
     * @param  Cell|null  $stepCell
     * @param  Cell|null  $destinationCell
     * @param  int        $step
     *
     * @return bool
     */
    private function findInTorpidoRange(?Cell $stepCell, ?Cell $destinationCell, int $step = 4): bool
    {
        if ($destinationCell == null || $destinationCell->isLand()) {
            return false;
        }

        if ($stepCell == null || $stepCell->isLand()) {
            return false;
        }

        if ($destinationCell === $stepCell) {
            return true;
        }

        if ($step == 0) {
            return false;
        }

        $inRange = false;

        $northCell = $stepCell->getNorthCell();
        if (!$inRange && $northCell != null && !$destinationCell->isLand()) {
            $inRange = $this->findInTorpidoRange($northCell, $destinationCell, $step - 1);
        }

        $eastCell = $stepCell->getEastCell();
        if (!$inRange && $eastCell != null && !$eastCell->isLand()) {
            $inRange = $this->findInTorpidoRange($eastCell, $destinationCell, $step - 1);
        }

        $westCell = $stepCell->getWestCell();
        if (!$inRange && $westCell != null && !$westCell->isLand()) {
            $inRange = $this->findInTorpidoRange($westCell, $destinationCell, $step - 1);
        }

        $southCell = $stepCell->getSouthCell();
        if (!$inRange && $southCell != null && !$southCell->isLand()) {
            $inRange = $this->findInTorpidoRange($southCell, $destinationCell, $step - 1);
        }

        return $inRange;
    }
}


$playingFirst = $myId == 0;
//Game initialisation
$player = new Player($board, null, $playingFirst);

// Write an action using echo(). DON'T FORGET THE TRAILING \n
// To debug: error_log(var_export($var, true)); (equivalent to var_dump)

//first print will be player position
$mySubX = $player->getMySub()->getCurrentCell()->getX();
$mySubY = $player->getMySub()->getCurrentCell()->getY();
echo("$mySubX $mySubY\n");

// game loop
while (true) {
    fscanf(STDIN, "%d %d %d %d %d %d %d %d", $x, $y, $myLife, $oppLife, $torpedoCooldown, $sonarCooldown, $silenceCooldown, $mineCooldown);
    fscanf(STDIN, "%s", $sonarResult);
    $opponentOrders = stream_get_line(STDIN, 200 + 1, "\n");

    $player
        ->getMySub()
        ->setSilenceCooldown($silenceCooldown)
        ->setTorpedoCooldown($torpedoCooldown)
        ->setMineCooldown($mineCooldown)
        ->setSonarCooldown($sonarCooldown);


    if ($player->getPlayingFirst()) {
        //$player->sonarResult($sonarResult);
        $myAction = $player->myNextAction();
        $player->translateEnemyActions($opponentOrders);
    } else {
        $player->translateEnemyActions($opponentOrders);
        //$player->sonarResult($sonarResult);

        $myAction = $player->myNextAction();
    }

    echo($myAction);
}
?>