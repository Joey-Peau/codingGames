<?php

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 * https://www.codingame.com/ide/puzzle/takuzu-solver
 * tests : 4/6 OK
 **/

class Cell
{
    private $x;
    private $y;
    private $value;

    public function __construct(int $x, int $y, ?int $value)
    {
        $this->x = $x;
        $this->y = $y;
        $this->value = $value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }
}

class Grid
{
    /** @var array<int, Cell[]> */
    private $cells = [];

    /**
     * @param  int  $length
     */
    public function __construct(int $length)
    {
        for ($i = 0; $i < $length; $i++) {
            for ($j = 0; $j < $length; $j++) {
                $this->cells[$i][$j] = new Cell($i, $j, null);
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
            switch ($input[$i]) {
                case '0':
                    $this->cells[$row][$i]->setValue(0);
                    break;
                case '1':
                    $this->cells[$row][$i]->setValue(1);
                    break;
                default:
                    $this->cells[$row][$i]->setValue(null);
            }
        }
    }

    /**
     * @param  int  $col
     *
     * @return array<Cell>
     */
    private function getColumn(int $col): array
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
    private function getRow(int $row): array
    {
        return $this->cells[$row];
    }

    private function getCell(int $x, int $y): Cell
    {
        return $this->cells[$x][$y];
    }

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

        //check middle
        if ($originCellIndex > 0 && $originCellIndex < count($cells) - 1) {
            if ($firstRight->getValue() !== null && $firstLeft->getValue() === $firstRight->getValue()) {
                $cells[$originCellIndex]->setValue(Grid::inverseValue($firstLeft->getValue()));

                return;
            }
        }

        //check left
        if ($originCellIndex > 1) {
            $secondLeft = $cells[$originCellIndex - 2];

            if ($secondLeft->getValue() !== null && $firstLeft->getValue() === $secondLeft->getValue()) {
                $cells[$originCellIndex]->setValue(Grid::inverseValue($firstLeft->getValue()));

                return;
            }
        }

        //check right
        if ($originCellIndex < count($cells) - 2) {
            $secondRight = $cells[$originCellIndex + 2];

            if ($secondRight->getValue() !== null && $firstRight->getValue() === $secondRight->getValue()) {
                $cells[$originCellIndex]->setValue(Grid::inverseValue($firstRight->getValue()));

                return;
            }
        }
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    private function solveConsecutiveColCell(int $row, int $col): void
    {
        $cells = $this->getColumn($col);
        $this->solveConsecutiveLineCell($cells, $row);
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    private function solveConsecutiveRowCell(int $row, int $col): void
    {
        $this->solveConsecutiveLineCell($this->cells[$row], $col);
    }

    private function solveFullRow(int $row): void
    {
        for ($i = 0; $i < count($this->cells[$row]); $i++) {
            $this->solveConsecutiveRowCell($row, $i);
        }
    }

    private function solveFullCol(int $col): void
    {
        for ($i = 0; $i < count($this->cells); $i++) {
            $this->solveConsecutiveColCell($i, $col);
        }
    }

    /**
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
     * @param  int  $col
     */
    private function solveSumCol(int $col): void
    {
        $listCols = $this->getColumn($col);
        $this->solveSumLine($listCols);
    }

    /**
     * @param  int  $row
     */
    private function solveSumRow(int $row): void
    {
        $this->solveSumLine($this->cells[$row]);
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    public function solveCell(int $row, int $col): void
    {
        if ($this->getCell($row, $col)->getValue() !== null) {
            return;
        }

        $this->solveConsecutiveRowCell($row, $col);
        $this->solveConsecutiveColCell($row, $col);
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
     * @return array|null
     */
    private function findNextPointer(?int $currentRow, ?int $currentCol): ?array
    {
        //error_log("\n");
        //error_log($this);
        if ($this->isComplete()) {
            return null;
        }

        for ($i = 0; $i < count($this->cells); $i++) {
            $this->solveFullRow($i);
            $this->solveSumRow($i);
            $this->solveFullCol($i);
            $this->solveSumCol($i);
        }

        for ($row = $currentRow ?? 0; $row < count($this->cells); $row++) {
            for ($col = 0; $col < count($this->cells); $col++) {
                if ($this->getCell($row, $col)->getValue() === null && $currentRow !== $row && $currentCol !== $col) {
                    return [$row, $col];
                }
            }
        }

        return $this->findNextPointer(null, null);
    }

    public function solve(): void
    {
        $pointer = $this->findNextPointer(null, null);

        while ($pointer !== null) {
            $this->solveCell($pointer[0], $pointer[1]);
            $pointer = $this->findNextPointer($pointer[0], $pointer[1]);
        }
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
            $string .= implode('', array_map(function (Cell $value) { return $value->getValue() === null ? '.' : $value->getValue(); }, $cells));
            if ($indexRow < count($this->cells) - 1) {
                $string .= "\n";
            }
        }

        return $string;
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

$grid->solve();

echo $grid;
?>
