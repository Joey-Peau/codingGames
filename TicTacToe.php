<?php

/**
 * Bot challenge : https://www.codingame.com/ide/puzzle/tic-tac-toe
 * PHP : 7.2
 *
 * @version 0.0
 * @test    Wood Ligue 1
 */
class Game
{
    /** @var ComplexGrid */
    private $gameGrid;
    /** @var SimpleGrid|null */
    private $workingGrid = null;
    /** @var array */
    private $workingCoordinates = [
        'gameRow' => null,
        'gameCol' => null,
        'gridRow' => null,
        'gridCol' => null,
    ];

    public function __construct()
    {
        $this->gameGrid = new ComplexGrid();
    }

    public function opponentMove(int $opponentRow, int $opponentCol): void
    {
        $coordinates = $this->getSubGridCellByGameRowAndCol($opponentRow, $opponentCol);

        //fetch opponent corresponding subgrid
        $subGrid = $this->gameGrid->getSubGrid($coordinates['gameRow'], $coordinates['gameCol']);
        //sets opponent value in the corresponding subgrid
        $subGrid->setValue($coordinates['gridRow'], $coordinates['gridCol'], Grid::__OPPONENT_SIDE);
        //update the grid we will work on for our next move
        $this->updateWorkingGrid($coordinates);
    }

    /**
     * @param  array{gameRow: int, gameCol: int, gridRow: int, gridCol: int}  $coord
     */
    private function updateWorkingGrid(array $coord): void
    {
        $this->workingCoordinates['gameRow'] = $coord['gridRow'];
        $this->workingCoordinates['gameCol'] = $coord['gridCol'];
        $this->workingCoordinates['gridRow'] = null;
        $this->workingCoordinates['gridCol'] = null;

        $this->workingGrid = $this->gameGrid->getSubGrid($this->workingCoordinates['gameRow'], $this->workingCoordinates['gameCol']);
    }

    /**
     * @return array{col: int, row: int}
     */
    public function fetchNextBestMove(): array
    {
        if($this->workingGrid !== null && $this->workingGrid->getWinningSide() !== null)
        {
            $this->workingGrid = null;
        }

        if ($this->workingGrid === null) {
            //TODO find best new working grid
            do {
                $gameRow = random_int(0, 2);
                $gameCol = random_int(0, 2);
                $this->workingGrid = $this->gameGrid->getSubGrid($gameRow, $gameCol);
            } while ($this->workingGrid->getWinningSide() !== null);

            $this->workingCoordinates['gameRow'] = $gameRow;
            $this->workingCoordinates['gameCol'] = $gameCol;
        }

        do {
            $currentRow = random_int(0, 2);
            $currentCol = random_int(0, 2);
            $currentValue = $this->workingGrid->getValue($currentRow, $currentCol);
        } while ($currentValue !== null);

        //TODO algo to find best next move for working grid
        $bestMoves = ['row' => $currentRow, 'col' => $currentCol];
        $this->workingCoordinates['gridRow'] = $bestMoves['row'];
        $this->workingCoordinates['gridCol'] = $bestMoves['col'];
        $this->workingGrid->setValue($bestMoves['row'], $bestMoves['col'], Grid::__PLAYER_SIDE);

        return $this->translateCellCoordToGameCoord($this->workingCoordinates);
    }

    /**
     * @param  int  $gameRow
     * @param  int  $gameCol
     *
     * @return array{gameRow: int, gameCol: int, gridRow: int, gridCol: int}
     */
    private function getSubGridCellByGameRowAndCol(int $gameRow, int $gameCol): array
    {
        $gridRow = $gameRow % Grid::__GRID_SIZE;
        $gridCol = $gameCol % Grid::__GRID_SIZE;

        $gameRow = (int)floor($gameRow / Grid::__GRID_SIZE);
        $gameCol = (int)floor($gameCol / Grid::__GRID_SIZE);

        return ['gameRow' => $gameRow, 'gameCol' => $gameCol, 'gridRow' => $gridRow, 'gridCol' => $gridCol];
    }

    /**
     * @param  array{gameRow: int, gameCol: int, gridRow: int, gridCol: int}  $coordinates
     *
     * @return array{col: int, row: int}
     */
    private function translateCellCoordToGameCoord(array $coordinates): array
    {
        $gameRow = $coordinates['gameRow'] * Grid::__GRID_SIZE + $coordinates['gridRow'];
        $gameCol = $coordinates['gameCol'] * Grid::__GRID_SIZE + $coordinates['gridCol'];

        return ['row' => $gameRow, 'col' => $gameCol];
    }
}

abstract class Grid
{
    public const __GRID_SIZE     = 3;
    public const __PLAYER_SIDE   = 'X';
    public const __OPPONENT_SIDE = 'O';
    /** @var array<int, array<int, string|null>>|array<int, array<int, SimpleGrid>> */
    protected $data = [];
    /** @var ?string */
    protected $cachedWinningValue = null;

    /**
     * @return string|null
     * @internal heavy load if no winning side found yet
     */
    public function getWinningSide(): ?string
    {
        //if winning side is already calculated
        if ($this->cachedWinningValue !== null) {
            return $this->cachedWinningValue;
        }

        //check row and column
        for ($i = 0; $i < Grid::__GRID_SIZE; $i++) {
            $winningRow = $this->getWinningRowSide($i);
            $winningCol = $this->getWinningColSide($i);

            if ($winningCol !== null) {
                $this->cachedWinningValue = $winningCol;

                return $this->cachedWinningValue;
            }

            if ($winningRow !== null) {
                $this->cachedWinningValue = $winningRow;

                return $this->cachedWinningValue;
            }
        }

        //check diagonal
        $winningDiag = $this->getWinningDiagonalSide();

        if ($winningDiag !== null) {
            $this->cachedWinningValue = $winningDiag;

            return $this->cachedWinningValue;
        }

        //no one won on this grid yet
        return null;
    }

    /**
     * @param  int  $row
     *
     * @return string|null
     */
    abstract public function getWinningRowSide(int $row): ?string;

    /**
     * @param  int  $col
     *
     * @return string|null
     */
    abstract public function getWinningColSide(int $col): ?string;

    /**
     * @return string|null
     */
    abstract public function getWinningDiagonalSide(): ?string;
}

class ComplexGrid extends Grid
{
    /** @var array<int, array<int, SimpleGrid>> */
    protected $data = [];

    public function __construct()
    {
        for ($i = 0; $i < Grid::__GRID_SIZE; $i++) {
            for ($j = 0; $j < Grid::__GRID_SIZE; $j++) {
                $this->data[$i][$j] = new SimpleGrid();
                $this->data[$i][$j]->setName("$i\_$j");
            }
        }
    }

    /**
     * @param  int  $row
     * @param  int  $col
     *
     * @return SimpleGrid
     */
    public function getSubGrid(int $row, int $col): SimpleGrid
    {
        return $this->data[$row][$col];
    }

    /**
     * @return string|null
     */
    public function getWinningDiagonalSide(): ?string
    {
        $middle = $this->getSubGrid(1, 1);

        $centerValue = $middle->getWinningSide();

        if ($centerValue === null) {
            return null;
        }

        $topLeft = $this->getSubGrid(0, 0);
        $bottomRight = $this->getSubGrid(2, 2);

        if ($topLeft->getWinningSide() === $centerValue && $centerValue === $bottomRight->getWinningSide()) {
            return $centerValue;
        }

        $bottomLeft = $this->getSubGrid(2, 0);
        $topRight = $this->getSubGrid(0, 2);

        if ($topRight->getWinningSide() === $centerValue && $centerValue === $bottomLeft->getWinningSide()) {
            return $centerValue;
        }

        return null;
    }

    /**
     * @param  int  $row
     *
     * @return string|null
     */
    public function getWinningRowSide(int $row): ?string
    {
        $middle = $this->getSubGrid($row, 1);

        $middleWinning = $middle->getWinningSide();

        if ($middleWinning === null) {
            return null;
        }

        $left = $this->getSubGrid($row, 0);
        $right = $this->getSubGrid($row, 2);

        if ($left->getWinningSide() === $middleWinning && $middleWinning === $right->getWinningSide()) {
            return $middleWinning;
        }

        return null;
    }

    /**
     * @param  int  $col
     *
     * @return string|null
     */
    public function getWinningColSide(int $col): ?string
    {
        $middle = $this->getSubGrid(1, $col);

        $middleValue = $middle->getWinningSide();

        if ($middleValue === null) {
            return null;
        }

        $top = $this->getSubGrid(0, $col);
        $bottom = $this->getSubGrid(2, $col);

        if ($top->getWinningSide() === $middleValue && $middleValue === $bottom->getWinningSide()) {
            return $middleValue;
        }

        return null;
    }
}

class SimpleGrid extends Grid
{
    /** @var array<int, array<int, string|null>> */
    protected $data = [];
    private $name = '';

    public function setName(string $name): SimpleGrid
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __construct()
    {
        for ($i = 0; $i < Grid::__GRID_SIZE; $i++) {
            for ($j = 0; $j < Grid::__GRID_SIZE; $j++) {
                $this->data[$i][$j] = null;
            }
        }
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @param  int          $row
     * @param  int          $col
     * @param  string|null  $value
     */
    public function setValue(int $row, int $col, ?string $value): void
    {
        $this->data[$row][$col] = $value;
    }

    public function getValue(int $row, int $col): ?string
    {
        return $this->data[$row][$col];
    }

    /**
     * @return string|null
     */
    public function getWinningDiagonalSide(): ?string
    {
        $centerValue = $this->data[1][1];

        if ($centerValue === null) {
            return null;
        }

        $topLeftValue = $this->data[0][0];
        $bottomRightValue = $this->data[2][2];

        if ($topLeftValue === $centerValue && $centerValue === $bottomRightValue) {
            return $centerValue;
        }

        $bottomLeftValue = $this->data[2][0];
        $topRightValue = $this->data[0][2];

        if ($bottomLeftValue === $centerValue && $centerValue === $topRightValue) {
            return $centerValue;
        }

        return null;
    }

    /**
     * @param  int  $row
     *
     * @return string|null
     */
    public function getWinningRowSide(int $row): ?string
    {
        $leftValue = $this->data[$row][0];
        $middleValue = $this->data[$row][1];
        $rightValue = $this->data[$row][2];

        if ($middleValue === null) {
            return null;
        }

        if ($leftValue === $middleValue && $middleValue === $rightValue) {
            return $middleValue;
        }

        return null;
    }

    /**
     * @param  int  $col
     *
     * @return string|null
     */
    public function getWinningColSide(int $col): ?string
    {
        $topValue = $this->data[0][$col];
        $middleValue = $this->data[1][$col];
        $bottomValue = $this->data[2][$col];

        if ($middleValue === null) {
            return null;
        }

        if ($topValue === $middleValue && $middleValue === $bottomValue) {
            return $middleValue;
        }

        return null;
    }
}

$game = new Game();

// game loop
while (true) {
    fscanf(STDIN, "%d %d", $opponentRow, $opponentCol);
    if ($opponentRow !== -1) {
        $game->opponentMove($opponentRow, $opponentCol);
    }
    fscanf(STDIN, "%d", $validActionCount);
    for ($i = 0; $i < $validActionCount; $i++) {
        fscanf(STDIN, "%d %d", $row, $col);
    }

    $move = $game->fetchNextBestMove();

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)

    echo($move['row'] . " " . $move['col'] . "\n");
    //echo("0 0\n");
}
