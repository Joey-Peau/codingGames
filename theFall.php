<?php
/**
 * https://www.codingame.com/ide/puzzle/the-fall-episode-1
 **/

class Maze
{
    /** @var Cell[][] */
    private $cells;

    public function __construct(int $width, int $height)
    {
        for ($line = 0; $line < $height; $line++) {
            for ($col = 0; $col < $width; $col++) {
                $this->cells[$line][$col] = null;
            }
        }
    }

    public function setCell(int $line, int $col, Cell $cell): void
    {
        $this->cells[$line][$col] = $cell;
    }

    public function getNextFallCoordinates(int $line, int $col, string $direction)
    {
        $currentCell = $this->cells[$line][$col];
        $fallDirection = $currentCell->fall($direction);

        $nextCellInput = $this->reverseDirection($fallDirection);
        $nextLine = $line;
        $nextCol = $col;

        switch ($fallDirection) {
            case Cell::__LEFT:
                $nextCol--;
                break;
            case Cell::__RIGHT:
                $nextCol++;
                break;
            case Cell::__BOTTOM:
                $nextLine++;
                break;
            case Cell::__TOP:
                $nextLine--;
                break;
        }

        return ['line' => $nextLine, 'col' => $nextCol, 'direction' => $nextCellInput];
    }

    public function reverseDirection(string $direction)
    {
        switch ($direction) {
            case Cell::__LEFT:
                return Cell::__RIGHT;
            case Cell::__RIGHT:
                return Cell::__LEFT;
            case Cell::__BOTTOM:
                return Cell::__TOP;
            case Cell::__TOP:
                return Cell::__BOTTOM;
        }

        throw new Exception('Should not happened');
    }
}

interface Cell
{
    public const __LEFT   = 'LEFT';
    public const __RIGHT  = 'RIGHT';
    public const __TOP    = 'TOP';
    public const __BOTTOM = 'BOTTOM';

    public function fall(string $input): ?string;
}

class Type0 implements Cell
{
    public function fall(string $input): ?string { return null; }
}

class Type1 implements Cell
{
    public function fall(string $input): ?string { return Cell::__BOTTOM; }
}

class Type2 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__LEFT:
                return Cell::__RIGHT;
            case Cell::__RIGHT:
                return Cell::__LEFT;
            case Cell::__TOP:
            default:
                return null;
        }
    }
}

class Type3 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
                return Cell::__BOTTOM;
            case Cell::__LEFT:
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

class Type4 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
                return Cell::__LEFT;
            case Cell::__RIGHT:
                return Cell::__BOTTOM;
            case Cell::__LEFT:
            default:
                return null;
        }
    }
}

class Type5 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
                return Cell::__RIGHT;
            case Cell::__LEFT:
                return Cell::__BOTTOM;
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

class Type6 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__LEFT:
                return Cell::__RIGHT;
            case Cell::__RIGHT:
                return Cell::__LEFT;
            case Cell::__TOP:
            default:
                return null;
        }
    }
}

class Type7 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
            case Cell::__RIGHT:
                return Cell::__BOTTOM;
            case Cell::__LEFT:
            default:
                return null;
        }
    }
}

class Type8 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__RIGHT:
            case Cell::__LEFT:
                return Cell::__BOTTOM;
            case Cell::__TOP:
            default:
                return null;
        }
    }
}

class Type9 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
            case Cell::__LEFT:
                return Cell::__BOTTOM;
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

class Type10 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
                return Cell::__LEFT;
            case Cell::__LEFT:
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

class Type11 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__TOP:
                return Cell::__RIGHT;
            case Cell::__LEFT:
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

class Type12 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__RIGHT:
                return Cell::__BOTTOM;
            case Cell::__TOP:
            case Cell::__LEFT:
            default:
                return null;
        }
    }
}

class Type13 implements Cell
{
    public function fall(string $input): ?string
    {
        switch ($input) {
            case Cell::__LEFT:
                return Cell::__BOTTOM;
            case Cell::__TOP:
            case Cell::__RIGHT:
            default:
                return null;
        }
    }
}

// $W: number of columns.
// $H: number of rows.
fscanf(STDIN, "%d %d", $W, $H);
$maze = new Maze($W, $H);
for ($line = 0; $line < $H; $line++) {
    $LINE = stream_get_line(STDIN, 200 + 1, "\n");// represents a line in the grid and contains W integers. Each integer represents one room of a given type.
    $listCell = explode(" ", $LINE);

    for ($col = 0; $col < count($listCell); $col++) {
        $cellType = "Type" . $listCell[$col];
        $maze->setCell($line, $col, new $cellType());
    }
}
// $EX: the coordinate along the X axis of the exit (not useful for this first mission, but must be read).
fscanf(STDIN, "%d", $EX);

// game loop
while (true) {
    fscanf(STDIN, "%d %d %s", $XI, $YI, $POS);

    $nextCell = $maze->getNextFallCoordinates($YI, $XI, $POS);

    $finalAnswer = $nextCell['col'] . ' ' . $nextCell['line'] . "\n";

    // Write an action using echo(). DON'T FORGET THE TRAILING \n
    // To debug: error_log(var_export($var, true)); (equivalent to var_dump)

    // One line containing the X Y coordinates of the room in which you believe Indy will be on the next turn.
    echo($finalAnswer);
}
?>
