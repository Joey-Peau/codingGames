<?php

trait Guessable
{
    protected $possibilities;

    public function isPossible(int $number): bool
    {
        return in_array($number, $this->possibilities);
    }

    public function removePossibility(int $number): bool
    {
        $key = array_search($number, $this->possibilities, true);
        if ($key !== false) {
            unset($this->possibilities[$key]);

            return true;
        }

        return false;
    }

    public function getPossibilities(): array
    {
        return $this->possibilities;
    }

}

abstract class Line
{
    use Guessable;

    protected $cells;

    public function __construct(int $gridSize)
    {
        for ($i = 1; $i <= $gridSize; $i++) {
            $this->possibilities[$i] = $i;
        }
    }

    public function addCell(Cell $cell)
    {
        $this->cells[] = $cell;
    }

    public function hasUniquePossible()
    {
        foreach ($this->possibilities as $possibility) {
            $isUnique = true;
            $uniqueCell = null;
            /** @var Cell $cell */
            foreach ($this->cells as $cell) {
                if ($cell->isPossible($possibility)) {
                    if ($uniqueCell === null) {
                        $uniqueCell = $cell;
                    } else {
                        $isUnique = false;
                        break;
                    }
                }
            }

            if ($uniqueCell !== null && $isUnique) {
                $uniqueCell->setValue($possibility);
            }
        }
    }
}

class Row extends Line
{
}

class Col extends Line
{
}

class Board
{

    private $cells    = [];
    /** @var Col[] */
    private $cols     = [];
    /** @var Row[] */
    private $rows     = [];
    private $isSolved = false;


    public function __construct(int $size)
    {
        $realSize = $size / 2;
        for ($i = 0; $i < $realSize; $i++) {
            $this->cols[] = new Col($realSize);
            $this->rows[] = new Row($realSize);
        }

        for ($i = 0; $i < $realSize; $i++) {
            for ($j = 0; $j < $realSize; $j++) {
                $cell = new Cell($realSize);
                $cell->setRow($this->rows[$i]);
                $cell->setCol($this->cols[$j]);
                $this->cells[$i][$j] = $cell;
            }
        }
    }

    public function build(int $rowIndex, string $line)
    {
        if (trim($line) == "") {
            return;
        }

        $colConnection = $rowIndex % 2 === 1;

        $realI = floor($rowIndex / 2);

        $nbCol = strlen($line);

        for ($i = 0; $i < $nbCol; $i++) {
            $value = $line[$i];

            if ($value === '0' || trim($value) == "") {
                continue;
            }

            $rowConnection = $i % 2 === 1;

            if ($colConnection && $rowConnection) {
                continue;
            }

            $realJ = floor($i / 2);

            if (!$colConnection && !$rowConnection) {
                $this->cells[$realI][$realJ]->setValue($value);
                continue;
            }

            if ($colConnection) {
                $firstCell  = &$this->cells[$realI][$realJ];
                $secondCell = &$this->cells[$realI + 1][$realJ];
                new Connection($firstCell, $value, $secondCell);
                unset($firstCell, $secondCell);
                continue;
            }

            if ($rowConnection) {
                $firstCell  = &$this->cells[$realI][$realJ];
                $secondCell = &$this->cells[$realI][$realJ + 1];
                new Connection($firstCell, $value, $secondCell);
                unset($firstCell, $secondCell);
                continue;
            }
        }
    }

    public function solve()
    {
        while (!$this->isSolved()) {
            foreach ($this->cells as $columnCell) {
                /** @var Cell $cell */
                foreach ($columnCell as $cell) {
                    $cell->update();
                    foreach ($cell->getConnections() as $connection) {
                        $connection->updateConnections();
                    }
                    $cell->update();
                }
            }

            foreach ($this->rows as $row) {
                $row->hasUniquePossible();
            }

            foreach ($this->cols as $col) {
                $col->hasUniquePossible();
            }

            error_log($this->__toString());
        }
    }

    public function isSolved()
    {
        return array_sum(array_map(static function ($data) { return count($data->getPossibilities()); }, $this->rows)) == 0;
    }

    public function __toString()
    {
        $fullString = "";
        foreach ($this->cells as $rowIndex => $rowCells) {
            foreach ($rowCells as $cell) {
                $fullString .= $cell->getValue();
            }
            $fullString .= "\n";
        }

        return $fullString;
    }

}

class Cell
{
    use Guessable;

    private $value = 0;
    /** @var Connection[] */
    private $connections = [];
    /** @var Col */
    private $col;
    /** @var Row */
    private $row;

    public function __construct(int $gridSize)
    {
        for ($i = 1; $i <= $gridSize; $i++) {
            $this->possibilities[$i] = $i;
        }
    }

    public function update()
    {
        if ($this->value != 0) {
            return;
        }

        $rowPossible = $this->row->getPossibilities();
        if (count($rowPossible) == 1) {
            $this->setValue(reset($rowPossible));

            return;
        }

        $colPossible = $this->col->getPossibilities();
        if (count($colPossible) == 1) {
            $this->setValue(reset($colPossible));

            return;
        }

        $toRemove = [];
        foreach ($this->possibilities as $possibility) {
            if (!$this->col->isPossible($possibility)) {
                $toRemove[] = $possibility;
                continue;
            }
            if (!$this->row->isPossible($possibility)) {
                $toRemove[] = $possibility;
                continue;
            }
        }

        foreach ($toRemove as $item) {
            $this->removePossible($item);
        }
    }

    public function setRow(Row $row): void
    {
        $this->row = $row;
        $this->row->addCell($this);
    }

    public function setCol(Col $col): void
    {
        $this->col = $col;
        $this->col->addCell($this);
    }

    public function getConnections(): array
    {
        return $this->connections;
    }

    public function setValue(int $value): void
    {
        $this->value         = $value;
        $this->possibilities = [];
        $this->col->removePossibility($value);
        $this->row->removePossibility($value);
    }

    public function addConnection(Connection $connection): void
    {
        $this->connections[] = $connection;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function removePossible(int $value): void
    {
        $this->removePossibility($value);

        if (count($this->possibilities) == 1) {
            $this->setValue(reset($this->possibilities));
        }
    }

}

class Connection
{
    private $first;
    private $eval;
    private $second;


    public function __construct(Cell $first, string $eval, Cell $second)
    {
        switch ($eval) {
            case "^":
                $realEval = "<";
                break;
            case "v":
                $realEval = ">";
                break;
            default:
                $realEval = $eval;
                break;
        }
        $this->first  = $first;
        $this->eval   = $realEval;
        $this->second = $second;

        $this->first->addConnection($this);
        $this->second->addConnection($this);
    }


    public function updateConnections(): bool
    {
        $updated = false;

        if ($this->first->getValue() != 0 && $this->second->getValue() == 0) {
            $secondPossibles = $this->second->getPossibilities();
            foreach ($secondPossibles as $secondPossible) {
                $isPossible = eval("return ".$this->first->getValue().$this->eval.$secondPossible.";");
                if ($isPossible === false) {
                    $this->second->removePossible($secondPossible);
                    $updated = true;
                }
            }
        }

        if ($this->first->getValue() == 0 && $this->second->getValue() != 0) {
            $firstPossibilities = $this->first->getPossibilities();
            foreach ($firstPossibilities as $firstPossibility) {
                $isPossible = eval("return ".$firstPossibility.$this->eval.$this->second->getValue().";");
                if ($isPossible === false) {
                    $this->first->removePossible($firstPossibility);
                    $updated = true;
                }
            }
        }

        if ($this->first->getValue() == 0 && $this->second->getValue() == 0) {
            $firstPossibilities  = $this->first->getPossibilities();
            $minFirst            = min($firstPossibilities);
            $maxFirst            = max($firstPossibilities);
            $secondPossibilities = $this->second->getPossibilities();
            $minSecond           = min($secondPossibilities);
            $maxSecond           = max($secondPossibilities);

            switch ($this->eval) {
                case "<":
                    $checkValueFirst  = $minFirst;
                    $checkValueSecond = $maxSecond;
                    break;
                case ">":
                    $checkValueFirst  = $maxFirst;
                    $checkValueSecond = $minSecond;
                    break;
            }

            foreach ($firstPossibilities as $firstPossibility) {
                $isPossible = eval("return $firstPossibility{$this->eval}$checkValueSecond;");
                if ($isPossible === false) {
                    $this->first->removePossible($firstPossibility);
                    $updated = true;
                }
            }

            foreach ($secondPossibilities as $secondPossible) {
                $isPossible = eval("return $checkValueFirst{$this->eval}$secondPossible;");
                if ($isPossible === false) {
                    $this->second->removePossible($secondPossible);
                    $updated = true;
                }
            }
        }

        if ($updated) {
            $secondConnections = $this->second->getConnections();
            $firstConnections  = $this->first->getConnections();
            foreach ($secondConnections as $secondConnection) {
                $secondConnection->updateConnections();
            }
            foreach ($firstConnections as $firstConnection) {
                $firstConnection->updateConnections();
            }
        }

        return $updated;
    }

}

/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 **/

fscanf(STDIN, "%d", $size);

$board = new Board($size + 1);

for ($i = 0; $i < $size; $i++) {
    $line = stream_get_line(STDIN, 256 + 1, "\n");
    $board->build($i, $line);
    error_log(var_export($line, true));
}

//error_log(var_export($board, true));
// Write an answer using echo(). DON'T FORGET THE TRAILING \n
// To debug: error_log(var_export($var, true)); (equivalent to var_dump)

error_log(var_export($board->__toString(), true));
for ($i = 0; $i <= 10; $i++) {
    $board->solve();
}
error_log(var_export($board->__toString(), true));

echo($board);