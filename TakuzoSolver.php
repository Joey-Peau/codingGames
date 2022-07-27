<?php

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 * https://www.codingame.com/ide/puzzle/takuzu-solver
 * tests : 4/6 OK
 **/
class Grid
{
    private $rows = [];

    /**
     * @param  int  $length
     */
    public function __construct(int $length)
    {
        for ($i = 0; $i < $length; $i++) {
            for ($j = 0; $j < $length; $j++) {
                $this->rows[$i][$j] = null;
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
                    $this->rows[$row][$i] = 0;
                    break;
                case '1':
                    $this->rows[$row][$i] = 1;
                    break;
                default:
                    $this->rows[$row][$i] = null;
            }
        }
    }

    /**
     * @param  int  $col
     *
     * @return array
     */
    private function getColumn(int $col): array
    {
        $finalArray = [];
        foreach ($this->rows as $row) {
            $finalArray[] = $row[$col];
        }

        return $finalArray;
    }

    /**
     * @param  int  $row
     *
     * @return array
     */
    private function getRow(int $row): array
    {
        return $this->rows[$row];
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    private function solveConsecutiveColCell(int $row, int $col): void
    {
        if ($this->rows[$row][$col] !== null) {
            return;
        }

        if ($row > 0) {
            $firstTop = $this->rows[$row - 1][$col];
        }

        if ($row < count($this->rows[$row]) - 1) {
            $firstBottom = $this->rows[$row + 1][$col];
        }

        //check middle
        if ($row > 0 && $row < count($this->rows) - 1) {
            if ($firstBottom !== null && $firstTop === $firstBottom) {
                $this->rows[$row][$col] = Grid::inverseValue($firstTop);

                return;
            }
        }

        //check top
        if ($row > 1) {
            $secondTop = $this->rows[$row - 2][$col];

            if ($secondTop !== null && $firstTop === $secondTop) {
                $this->rows[$row][$col] = Grid::inverseValue($firstTop);

                return;
            }
        }

        //check bottom
        if ($row < count($this->rows) - 2) {
            $secondBottom = $this->rows[$row + 2][$col];

            if ($secondBottom !== null && $firstBottom === $secondBottom) {
                $this->rows[$row][$col] = Grid::inverseValue($firstBottom);

                return;
            }
        }
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    private function solveConsecutiveRowCell(int $row, int $col): void
    {
        if ($this->rows[$row][$col] !== null) {
            return;
        }

        if ($col > 0) {
            $firstLeft = $this->rows[$row][$col - 1];
        }

        if ($col < count($this->rows[$row]) - 1) {
            $firstRight = $this->rows[$row][$col + 1];
        }

        //check middle
        if ($col > 0 && $col < count($this->rows) - 1) {
            if ($firstRight !== null && $firstLeft === $firstRight) {
                $this->rows[$row][$col] = Grid::inverseValue($firstLeft);

                return;
            }
        }

        //check left
        if ($col > 1) {
            $secondLeft = $this->rows[$row][$col - 2];

            if ($secondLeft !== null && $firstLeft === $secondLeft) {
                $this->rows[$row][$col] = Grid::inverseValue($firstLeft);

                return;
            }
        }

        //check right
        if ($col < count($this->rows) - 2) {
            $secondRight = $this->rows[$row][$col + 2];

            if ($secondRight !== null && $firstRight === $secondRight) {
                $this->rows[$row][$col] = Grid::inverseValue($firstRight);

                return;
            }
        }
    }

    private function solveFullRow(int $row): void
    {
        for ($i = 0; $i < count($this->rows[$row]); $i++) {
            $this->solveConsecutiveRowCell($row, $i);
        }
    }

    private function solveFullCol(int $col): void
    {
        for ($i = 0; $i < count($this->rows); $i++) {
            $this->solveConsecutiveColCell($i, $col);
        }
    }

    /**
     * @param  int  $row
     */
    private function solveSumRow(int $row): void
    {
        $countEmptyCells = 0;
        foreach ($this->rows[$row] as $value) {
            if ($value === null) {
                $countEmptyCells++;
            }
        }

        //when not cell is empty, return
        if ($countEmptyCells === 0) {
            return;
        }

        $sum = 0;
        foreach ($this->rows[$row] as $value) {
            if ($value !== null) {
                $sum += $value;
            }
        }

        //when all 1 completed
        if ($sum === count($this->rows) / 2) {
            foreach ($this->rows[$row] as $col => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 0;
                }
            }

            return;
        }

        //when all zeros completed
        if ($sum === 0 && $countEmptyCells === count($this->rows) / 2) {
            foreach ($this->rows[$row] as $col => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 1;
                }
            }

            return;
        }

        //when empty cells plus the sum is half the grid, the empty cells can only be ones
        if ($sum + $countEmptyCells === count($this->rows) / 2) {
            foreach ($this->rows[$row] as $col => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 1;
                }
            }

            return;
        }
    }

    private function solveImpossibleChoiceRow(int $row, int $col): void
    {
        //get the number of missing zeros and ones
        $countZeros = 0;
        $countOnes = 0;
        foreach ($this->rows[$row] as $value) {
            if ($value === 0) {
                $countZeros++;
            } elseif ($value === 1) {
                $countOnes++;
            }
        }
        $missingZeros = count($this->rows) / 2 - $countZeros;
        $missingOnes = count($this->rows) / 2 - $countOnes;
        //todo check that if a zeros (or one) is placed here, it's would not be valid for the rest of the row.

    }

    /**
     * @param  int  $col
     */
    private function solveSumCol(int $col): void
    {
        $listCols = $this->getColumn($col);

        $countEmptyCells = 0;
        foreach ($listCols as $value) {
            if ($value === null) {
                $countEmptyCells++;
            }
        }

        //when not cell is empty, return
        if ($countEmptyCells === 0) {
            return;
        }

        $sum = 0;
        foreach ($listCols as $value) {
            if ($value !== null) {
                $sum += $value;
            }
        }

        //when all 1 completed the rest can only be zeros
        if ($sum === count($this->rows) / 2) {
            foreach ($listCols as $row => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 0;
                }
            }

            return;
        }

        //when all zeros completed the rest can only be ones
        if ($sum === 0 && $countEmptyCells === count($this->rows) / 2) {
            foreach ($listCols as $row => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 1;
                }
            }

            return;
        }

        //when empty cells plus the sum is half the grid, the empty cells can only be ones
        if ($sum + $countEmptyCells === count($this->rows) / 2) {
            foreach ($listCols as $row => $value) {
                if ($value === null) {
                    $this->rows[$row][$col] = 1;
                }
            }

            return;
        }
    }

    /**
     * @param  int  $row
     * @param  int  $col
     */
    public function solveCell(int $row, int $col): void
    {
        if ($this->rows[$row][$col] !== null) {
            return;
        }

        $this->solveConsecutiveRowCell($row, $col);
        $this->solveConsecutiveColCell($row, $col);
        //$this->solveImpossibleChoiceRow($row, $col);
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        foreach ($this->rows as $row) {
            foreach ($row as $value) {
                if ($value === null) {
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

        for ($i = 0; $i < count($this->rows); $i++) {
            $this->solveFullRow($i);
            $this->solveSumRow($i);
            $this->solveFullCol($i);
            $this->solveSumCol($i);
        }

        for ($row = $currentRow ?? 0; $row < count($this->rows); $row++) {
            for ($col = 0; $col < count($this->rows); $col++) {
                if ($this->rows[$row][$col] === null && $currentRow !== $row && $currentCol !== $col) {
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
        foreach ($this->rows as $indexRow => $row) {
            $string .= implode('', array_map(function ($value) { return $value === null ? '.' : $value; }, $row));
            if ($indexRow < count($this->rows) - 1) {
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
