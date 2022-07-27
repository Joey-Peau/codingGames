<?php
/**
 * Auto-generated code below aims at helping you parse
 * the standard input according to the problem statement.
 * https://www.codingame.com/ide/puzzle/retro-typewriter-art
 **/

$T = stream_get_line(STDIN, 1000 + 1, "\n");



$array = explode(' ', $T);

$string = '';
foreach ($array as $item) {

    if($item === "nl") {
        $string .= PHP_EOL;
        continue;
    }

    $found = preg_match('/^(\d+)(\d{1}|.+)$/', $item, $matches);

    $times = $matches[1];
    $char = $matches[2];

    switch($char) {
        case 'sp':
            $string .= str_repeat(' ', $times);
            break;
        case 'bS':
            $string .= str_repeat('\\', $times);
            break;
        case 'sQ':
            $string .= str_repeat('\'', $times);
            break;
        case 'nl':
            $string .= str_repeat(PHP_EOL, $times);
            break;
        default:
            $string .= str_repeat($char, $times);
            break;
    }
}

// Write an answer using echo(). DON'T FORGET THE TRAILING \n
// To debug: error_log(var_export($var, true)); (equivalent to var_dump)

echo("$string");
?>
