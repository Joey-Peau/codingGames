<?php

const __BLOCK_LENGTH = 10;

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

$board = new Board(10);

$motherCell = $childCell = null;

for ($y = 0; $y < 10; $y++) {
    $line       = stream_get_line(STDIN, 10 + 1, "\n");
    $lengthLine = strlen($line);
    for ($x = 0; $x < $lengthLine; $x++) {
        if ($line[$x] === "#") {
            $board->getCell($x, $y)->setIsWall(true);
        }else{
            $board->getCell($x, $y)->setIsWall(false);
        }

        if ($line[$x] === "M") {
            $motherCell = $board->getCell($x, $y);
        } elseif ($line[$x] === "C") {
            $childCell = $board->getCell($x, $y);
        }
    }
}

class Board
{

    private $width;
    private $height;

    private $cellsCoordinates = [];

    public function __construct(int $size)
    {
        $this->size = $size;

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $this->cellsCoordinates[$y][$x] = new Cell($this, $x, $y);
            }
        }
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
            if ($y < 0 || $y > $this->size - 1) {
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
                if ($x < 0 || $x > $this->size - 1) {
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
}


class Cell
{
    /** @var bool */
    private $isWall;
    /** @var Board */
    private $board;

    private $minLengthFromChild = 100000;

    public function __construct(Board $board, int $x, int $y, ?bool $isWall = false)
    {
        $this->board  = $board;
        $this->x      = $x;
        $this->y      = $y;
        $this->isWall = $isWall;
    }

    public function setMinLengthFromChild(int $value): void
    {
        $this->minLengthFromChild = min($this->minLengthFromChild, $value);
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

    public function isWall(): bool
    {
        return $this->isWall;
    }

    public function getMinStepFromChild(): int
    {
        return $this->minLengthFromChild;
    }

    public function setIsWall(bool $isWall): self
    {
        $this->isWall = $isWall;

        return $this;
    }
}

class ParalelePathFinder
{

}

class Player
{

    protected $board;
    protected $mother;
    protected $child;

    /** @var Cell[] */
    protected $forNextStep = [];

    public function __construct(Board $board, Cell $childCell, Cell $motherCell)
    {
        $this->board  = $board;
        $this->mother = $motherCell;
        $this->child  = $childCell;
        $this->child->setMinLengthFromChild(0);
    }

    public function getMotherCell(){
        return $this->mother;
    }

    public function buildPathToMother()
    {
        $this->nextStepCellPath($this->child, 0);
        $this->recursivePathFinder(0);
    }

    public function recursivePathFinder(int $step): void
    {
        if (count($this->forNextStep) == 0) {
            return;
        }

        $currentStep       = $this->forNextStep;
        $this->forNextStep = [];

        $foundMother = false;

        foreach ($currentStep as $cell) {
            $foundMother = $foundMother || $this->nextStepCellPath($cell, $step + 1);
        }

        if ($foundMother) {
            return;
        }

        $this->recursivePathFinder($step + 1);
    }

    public function nextStepCellPath(Cell $cell, int $step): bool
    {
        $cell->setMinLengthFromChild($step);
        if ($this->mother === $cell) {
            return true;
        }

        $northCell = $cell->getNorthCell();
        if ($northCell !== null && !$northCell->isWall()) {
            if ($northCell->getMinStepFromChild() > $step) {
                $this->forNextStep[] = $northCell;
            }
        }

        $eastCell = $cell->getEastCell();
        if ($eastCell !== null && !$eastCell->isWall()) {
            if ($eastCell->getMinStepFromChild() > $step) {
                $this->forNextStep[] = $eastCell;
            }
        }

        $westCell = $cell->getWestCell();
        if ($westCell !== null && !$westCell->isWall()) {
            if ($westCell->getMinStepFromChild() > $step) {
                $this->forNextStep[] = $westCell;
            }
        }

        $southCell = $cell->getSouthCell();
        if ($southCell !== null && !$southCell->isWall()) {
            if ($southCell->getMinStepFromChild() > $step) {
                $this->forNextStep[] = $southCell;
            }
        }

        return false;
    }

}

$player = new Player($board, $childCell, $motherCell);
$player->buildPathToMother();

// Write an answer using echo(). DON'T FORGET THE TRAILING \n
// To debug: error_log(var_export($var, true)); (equivalent to var_dump)

echo($player->getMotherCell()->getMinStepFromChild() * __BLOCK_LENGTH ."km\n");
?>