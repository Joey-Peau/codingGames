<?php

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 * https://www.codingame.com/ide/puzzle/takuzu-solver
 * tests : 6/6 OK
 **/

class Cell
{
    /** @var int */
    private $row;
    /** @var int */
    private $column;
    /** @var int|null */
    private $value;

    /**
     * @param  int       $row
     * @param  int       $col
     * @param  int|null  $value
     */
    public function __construct(int $row, int $col, ?int $value)
    {
        $this->row = $row;
        $this->column = $col;
        $this->value = $value;
    }

    /**
     * @param  int|null  $value
     */
    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    /**
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }
}

/**
 *
 */
class Grid
{
    /** @var array<int, Cell[]> */
    private $cells = [];

    /**
     * @return int
     */
    public function getSize(): int
    {
        return count($this->cells);
    }

    /**
     * @param  int  $length
     */
    public function __construct(int $length)
    {
        for ($row = 0; $row < $length; $row++) {
            for ($col = 0; $col < $length; $col++) {
                $this->cells[$row][$col] = new Cell($row, $col, null);
            }
        }
    }

    /**
     * @param  int     $row
     * @param  string  $input
     */
    public function buildRow(int $row, string $input): void
    {
        //foreach char of the string, set the value of the grid at the given row and column
        for ($i = 0; $i < strlen($input); $i++) {
            $cell = $this->getCell($row, $i);
            switch ($input[$i]) {
                case '0':
                    $cell->setValue(0);
                    break;
                case '1':
                    $cell->setValue(1);
                    break;
                default:
                    $cell->setValue(null);
            }
        }
    }

    /**
     * @param  int  $col
     *
     * @return array<Cell>
     */
    public function getColumn(int $col): array
    {
        $finalArray = [];
        foreach ($this->cells as $cell) {
            $finalArray[] = $cell[$col];
        }

        return $finalArray;
    }

    /**
     * @param  int  $row
     *
     * @return array<Cell>
     */
    public function getRow(int $row): array
    {
        return $this->cells[$row];
    }

    /**
     * @return array<int, Cell[]>
     */
    public function getRows(): array
    {
        $rows = [];

        for ($i = 0; $i < $this->getSize(); $i++) {
            $rows[] = $this->getRow($i);
        }

        return $rows;
    }

    /**
     * @return array<int, Cell[]>
     */
    public function getColumns(): array
    {
        $columns = [];

        for ($i = 0; $i < $this->getSize(); $i++) {
            $columns[] = $this->getColumn($i);
        }

        return $columns;
    }

    /**
     * @param  int  $x
     * @param  int  $y
     *
     * @return Cell
     */
    public function getCell(int $x, int $y): Cell
    {
        return $this->cells[$x][$y];
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        foreach ($this->cells as $rowCell) {
            foreach ($rowCell as $cell) {
                if ($cell->getValue() === null) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  int|null  $currentRow
     * @param  int|null  $currentCol
     *
     * @return Cell|null
     */
    public function findNextEmptyCell(?int $currentRow, ?int $currentCol): ?Cell
    {
        error_log("\n");
        error_log($this);
        if ($this->isComplete()) {
            return null;
        }

        for ($row = $currentRow ?? 0; $row < count($this->cells); $row++) {
            for ($col = 0; $col < count($this->cells); $col++) {
                $cell = $this->getCell($row, $col);
                if ($cell->getValue() === null && $currentRow !== $row && $currentCol !== $col) {
                    return $cell;
                }
            }
        }

        return $this->findNextEmptyCell(null, null);
    }

    /**
     * @param  int  $value
     *
     * @return int
     */
    public static function inverseValue(int $value): int
    {
        return $value === 0 ? 1 : 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this->cells as $indexRow => $cells) {
            $string .= implode(
                '',
                array_map(function (Cell $value) {
                    return $value->getValue() === null ? '.' : $value->getValue();
                }, $cells)
            );
            if ($indexRow < count($this->cells) - 1) {
                $string .= "\n";
            }
        }

        return $string;
    }
}

/**
 * Solve a grid respecting these three rules :
 * <ol>
 *  <li>There can never be three consecutive cells of the same value in a line.</li>
 *  <li>Each line (horizontal or vertical) must contain the same number of 1s and 0s.</li>
 *  <li>Each line must be unique (no two lines can be the same).</li>
 * </ol>
 */
class Solver
{
    /** @var Grid */
    private $grid;
    /** @var int */
    public static $iteration = 0;

    /**
     * @param  Grid  $grid
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Try to solve the grid.
     */
    public function solve(): void
    {
        $pointer = $this->grid->findNextEmptyCell(null, null);

        self::$iteration++;

        $gridSize = $this->grid->getSize();

        while ($pointer !== null) {
            for ($i = 0; $i < $gridSize; $i++) {
                $this->solveFullRow($i);
                $this->solveFullCol($i);
            }

            $this->solveCell($pointer);
            $pointer = $this->grid->findNextEmptyCell($pointer->getRow(), $pointer->getColumn());

            self::$iteration++;
        }
    }

    /**
     * Try solving a line respecting the three differents rules
     *
     * @param  array  $listOfLines
     * @param  int    $indexInList
     */
    private function solveFullLine(array $listOfLines, int $indexInList): void
    {
        $cells = $listOfLines[$indexInList];

        foreach ($cells as $indexOrigin => $cell) {
            $this->solveConsecutiveLineCell($cells, $indexOrigin);
        }

        $this->solveRemaindersLine($cells);

        $this->solveSumLine($cells);

        $this->solveIdenticalLines($listOfLines, $indexInList);
    }

    /**
     * Given a cell of origin and the line of cell it's in, solve the grid respecting the first rule
     *
     * @param  array<Cell>  $cells
     * @param  int          $originCellIndex
     */
    private function solveConsecutiveLineCell(array $cells, int $originCellIndex): void
    {
        if ($cells[$originCellIndex]->getValue() !== null) {
            return;
        }

        if ($originCellIndex > 0) {
            $firstLeft = $cells[$originCellIndex - 1];
        }

        if ($originCellIndex < count($cells) - 1) {
            $firstRight = $cells[$originCellIndex + 1];
        }

        //Find pairs left
        if ($originCellIndex > 1) {
            $secondLeft = $cells[$originCellIndex - 2];

            if ($firstLeft->getValue() !== null && $firstLeft->getValue() === $secondLeft->getValue()) {
                $cells[$originCellIndex]->setValue(Grid::inverseValue($firstLeft->getValue()));

                return;
            }
        }

        //Find pairs right
        if ($originCellIndex < count($cells) - 2) {
            $secondRight = $cells[$originCellIndex + 2];

            if ($firstRight->getValue() !== null && $firstRight->getValue() === $secondRight->getValue()) {
                $cells[$originCellIndex]->setValue(Grid::inverseValue($firstRight->getValue()));

                return;
            }
        }

        //prevent trios
        if ($originCellIndex > 0) {
            if ($originCellIndex < count($cells) - 1) {
                if ($firstLeft->getValue() !== null && $firstLeft->getValue() === $firstRight->getValue()) {
                    $cells[$originCellIndex]->setValue(Grid::inverseValue($firstLeft->getValue()));

                    return;
                }
            }
        }
    }

    /**
     * Solve a line based on the second rule.
     *
     * @param  array<Cell>  $line
     */
    private function solveSumLine(array $line): void
    {
        $countEmptyCells = 0;
        foreach ($line as $cell) {
            if ($cell->getValue() === null) {
                $countEmptyCells++;
            }
        }

        //when not cell is empty, return
        if ($countEmptyCells === 0) {
            return;
        }

        $sum = 0;
        foreach ($line as $cell) {
            if ($cell->getValue() !== null) {
                $sum += $cell->getValue();
            }
        }

        //when all 1 completed
        if ($sum === count($line) / 2) {
            foreach ($line as $col => $cell) {
                if ($cell->getValue() === null) {
                    $cell->setValue(0);
                }
            }

            return;
        }

        //when all zeros completed
        if ($sum === 0 && $countEmptyCells === count($line) / 2) {
            foreach ($line as $cell) {
                if ($cell->getValue() === null) {
                    $cell->setValue(1);
                }
            }

            return;
        }

        //when empty cells plus the sum is half the grid, the empty cells can only be ones
        if ($sum + $countEmptyCells === count($line) / 2) {
            foreach ($line as $cell) {
                if ($cell->getValue() === null) {
                    $cell->setValue(1);
                }
            }

            return;
        }
    }

    /**
     * Solve a line based on the third rule.
     *
     * @param  array<int, Cell[]>  $comparaisonLines
     * @param  int                 $lineIndex
     */
    private function solveIdenticalLines(array $comparaisonLines, int $lineIndex): void
    {
        $isCellEmpty = function (Cell $cell) { return $cell->getValue() === null; };

        $line = $comparaisonLines[$lineIndex];

        $missingInLine = array_filter($line, $isCellEmpty);

        //we don't care if the line has not exactly two unset cells
        if (count($missingInLine) !== 2) {
            return;
        }

        foreach ($comparaisonLines as $indexComparaison => $comparaisonLine) {
            //we do not compare the same line
            if ($indexComparaison === $lineIndex) {
                continue;
            }

            $missingInCurrentLine = count(array_filter($comparaisonLine, $isCellEmpty));

            //we don't care about uncompleted lines
            if ($missingInCurrentLine !== 0) {
                continue;
            }

            //let's compare if the two lines are differents
            foreach ($line as $indexCell => $cell) {
                //both line are stricly different
                if ($cell->getValue() !== null && $cell->getValue() !== $comparaisonLine[$indexCell]->getValue()) {
                    continue 2;
                }
            }

            //let's set the missing cells
            foreach ($line as $indexCell => $cell) {
                if ($cell->getValue() === null) {
                    $cell->setValue(Grid::inverseValue($comparaisonLine[$indexCell]->getValue()));
                }
            }

            return;
        }
    }

    /**
     * Solve a line based on the first and second rule eliminating impossible values.
     *
     * @param  array<Cell>  $line
     */
    private function solveRemaindersLine(array $line): void
    {
        //fetch the number of 1s and 0s to fill in the line
        $countOfOnes = 0;
        $countOfZeros = 0;
        foreach ($line as $cell) {
            if ($cell->getValue() === null) {
                continue;
            }

            if ($cell->getValue() === 1) {
                $countOfOnes++;
            } else {
                $countOfZeros++;
            }
        }

        $missingOnes = count($line) / 2 - $countOfOnes;
        $missingZeros = count($line) / 2 - $countOfZeros;

        if ($missingOnes !== 1 && $missingZeros !== 1) {
            return;
        }

        $valueToTest = $missingOnes === 1 ? 1 : 0;

        $missingIndexes = [];
        foreach ($line as $indexCell => $cell) {
            if ($cell->getValue() === null) {
                $missingIndexes[] = $indexCell;
            }
        }

        //we put 1 at every missing place and fill the others with 0s until one combination makes the line invalid
        foreach ($missingIndexes as $indexCell) {
            $clonedArray = array_map(function (Cell $cell) { return clone $cell; }, $line);

            if ($clonedArray[$indexCell]->getValue() === null) {
                $clonedArray[$indexCell]->setValue($valueToTest);
            }

            foreach ($missingIndexes as $missingIndex) {
                if ($clonedArray[$missingIndex]->getValue() === null) {
                    $clonedArray[$missingIndex]->setValue(Grid::inverseValue($valueToTest));
                }
            }

            //we finally have a combination that makes the line invalid, the the value must be the other one
            if (!$this->isLineValid($clonedArray)) {
                $line[$indexCell]->setValue(Grid::inverseValue($valueToTest));

                return;
            }
        }
    }

    /**
     * @param  array<Cell>  $line
     *
     * @return bool
     */
    private function isLineValid(array $line): bool
    {
        $secondLeft = $line[0];
        $firstLeft = $line[1];
        $sum = 0;
        foreach ($line as $indexCell => $cell) {
            $sum += $cell->getValue();

            //we skip the first two cells
            if ($indexCell < 2) {
                continue;
            }

            //check the first rule
            if ($secondLeft->getValue() === $firstLeft->getValue() && $firstLeft->getValue() === $cell->getValue()) {
                return false;
            }

            $secondLeft = $firstLeft;
            $firstLeft = $cell;
        }

        //check the second rule
        if ($sum !== count($line) / 2) {
            return false;
        }

        //TODO check identical lines (third rule)

        return true;
    }

    /**
     * @param  array<Cell>  $line
     *
     * @return bool
     */
    private function isLineCompleted(array $line): bool
    {
        foreach ($line as $cell) {
            if ($cell->getValue() === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Cell  $cell
     */
    private function solveConsecutiveColCell(Cell $cell): void
    {
        $cells = $this->grid->getColumn($cell->getColumn());
        $this->solveConsecutiveLineCell($cells, $cell->getRow());
    }

    /**
     * @param  Cell  $cell
     */
    private function solveConsecutiveRowCell(Cell $cell): void
    {
        $this->solveConsecutiveLineCell($this->grid->getRow($cell->getRow()), $cell->getColumn());
    }

    /**
     * Try solving a row
     *
     * @param  int  $row
     */
    private function solveFullRow(int $row): void
    {
        $this->solveFullLine($this->grid->getRows(), $row);
    }

    /**
     * Try solving a column
     *
     * @param  int  $col
     */
    private function solveFullCol(int $col): void
    {
        $this->solveFullLine($this->grid->getColumns(), $col);
    }

    /**
     * @param  Cell  $cell
     */
    private function solveCell(Cell $cell): void
    {
        if ($cell->getValue() !== null) {
            return;
        }

        $this->solveConsecutiveRowCell($cell);
        $this->solveConsecutiveColCell($cell);
    }
}

fscanf(STDIN, "%d", $n);
$grid = new Grid($n);
for ($i = 0; $i < $n; $i++) {
    $input = stream_get_line(STDIN, 40 + 1, "\n");
    $grid->buildRow($i, $input);
}

error_log($grid);
error_log("\n");

$solver = new Solver($grid);

$solver->solve();
error_log($solver::$iteration);

echo $grid;
?>
