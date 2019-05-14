<?php

// Call the driver here instead of adding this at the bottom of
// the reservationDriver class file
require_once 'src/reservationDriver.php';

$start = microtime(true);
$initalMemStr = "Initial Mem Usage: ". round(memory_get_usage() / 1024) . "KB\r\n";
$initialPeakMemStr = "Initial Peak Mem Usage: ". round(memory_get_peak_usage() / 1024) . "KB\r\n";

// Get the input, instantiate the class and run it
$input = "";
while($f = fgets(STDIN)){
    $input .= $f;
}

try {
    $driver = new ReservationDriver($input);
    $output = $driver->runSeatingChart(3, 11,'R1C6');
    fwrite(STDOUT, $output);
}
catch (Exception $e) {
    echo ($e->getMessage());
}

/*
$val = 1 <<3 | 0xC0DE;
echo $val . "@showclix.com";
*/

/*
echo "\nPerformance Stats\r\n";
echo "--------------------\r\n";
echo $initalMemStr;
echo $initialPeakMemStr;
echo "Final Mem Usage: ". round(memory_get_usage() / 1024) . "KB\r\n";
echo "Final Peak Mem Usage: ". round(memory_get_peak_usage() / 1024) . "KB\r\n";
echo "Time: " . round(microtime(true) - $start, 4) . " seconds.\r\n";
*/

?>