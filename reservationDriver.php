<?php 

require_once 'seatingChart.php';

/**
 * Driver class for reservations for a seating chart program
 *
 * @author Nick Michaels <nickmichaels@gmail.com>
 */
class ReservationDriver {

    /**
     * Property for SeatingChart instance, used to access some of that 
     * class's underlying properties when displaying output in this driver
     *
     * @var Instance of SeatingChart
     */
    protected $_seatingChart;

    /**
     * String storing the input passed into the class
     *
     * @var string
     */
    protected $_inputStr;

    /**
     * Boolean value whether or not to display the seating charts
     * for the final state of the seating chart and the manhattan distances
     * of each seat from the "best seat"
     *
     * @var bool
     */
    protected $_displayCharts;

    /**
     * Create a new ReservationDriver
     *
     * @param string $table
     * @param bool $displayCharts
     * @return void
     */
    public function __construct($input, $displayCharts = true) {
        // Assign properties here
        $this->_inputStr = $input;
        $this->_displayCharts = $displayCharts;
    }

    /**
     * Main driver function of the program
     *
     * This instantiates a SeatingChart instance,
     * parses input from stdin, assigns seats
     * and outputs to stdout 
     *
     * @param int $rows
     * @param int $columns
     * @param string $bestSeat
     * @return void
     */
    public function runSeatingChart($rows, $columns, $bestSeat) {
        // Validate that the best seat is within the bounds of the rows and columns
        $bestArr = SeatingChart::parseBestSeat($bestSeat);

        if ( (intval($rows) < 1) || (intval($columns) < 1) ) {
            $error = "Program instantiating with invalid parameters for rows $rows and / or columns $columns. Aborting program. \r\n";
            throw new Exception($error);
            exit(1);         
        }

        if ( ($bestArr['bestRow'] > $rows) || ($bestArr['bestCol'] > $columns) ) {
            $error = "Best seat at coordinates " . $bestSeat . " is out of bounds. Aborting program. \r\n";
            throw new Exception($error);
            exit(1);
        }
        $this->_seatingChart = new SeatingChart($rows, $columns, $bestSeat);
        $this->parseInput();

        $output = $this->assignSeats();

        // Show the number of seats remaining
        $output .= $this->_seatingChart->getSeatsAvailable();

        if ($this->_displayCharts) {
            $output .= "\r\n" . $this->displaySeatingChart('value');
            $output .= $this->displaySeatingChart('manhattan');  
        } 

        fwrite(STDOUT, $output);
    }
    
    /**
     * Parse the input passed into the class into a format that we can easily use
     *
     * This looks at the string in $this->_inputStr, parses it from
     * the format that we expect to divide the data into 
     * initial reservations of exact seats and secondary or "best available"
     * reservations of X number of consecutive seats
     *
     * @param void
     * @return void
     */
    public function parseInput( ) {
        // Get this first part up to a newline character, these are the initial reservations
        $inputArr = explode("\n", $this->_inputStr);
        // Explode this into an array so that the data structure class can handle it easier
        $this->_initRes = explode(" ", $inputArr[0]);
        // Store the other values as the secondary / "best available" reservations
        $this->_secRes = array();
        for ($i = 1; $i < count($inputArr); $i++) {
            // Validate for empty lines on the input string here
            if (intval($inputArr[$i]) > 0) {
                $this->_secRes[] = intval($inputArr[$i]);
            }
        }
    }

    /**
     * Call on the SeatingChart instance to process the reservations
     *
     * This uses methods in the SeatingChart instance to process the reservations
     * and handle any errors that come back and returns any seat reservation
     * info and error back
     *
     * @param void
     * @return string
     */
    public function assignSeats() {
        $output = "";

        // Assign primary reservations
        try {
            $this->_seatingChart->handleInitialReservations($this->_initRes);
        }
        catch (Exception $e) {
            throw $e;
            exit(1);
        }

        // Assign secondary best available reservations
        $outArr = $this->_seatingChart->handleSecondaryReservations($this->_secRes);
        foreach ($outArr as $str) {
            $output .= $str . "\r\n";
        }

        return $output;
    }

    /**
     * Display the seating chart in a simple plain text representation
     *
     * This reads the data structure and displays it as a chart
     * This can show either all of the seats booked or
     * the "manhattan distance" from the best seat for each seat in the chart
     *
     * @param string $type
     * @return string
     */
    public function displaySeatingChart($type) {
        // Handle bad $types here
        if (!in_array($type, array('value','manhattan'))) {
            return false;
        }
        $seatingChartData = $this->_seatingChart->getData();
        // Header row
        $str = "|" . ucfirst($type) . "|\r\n";
        $str .= "|~";
        for ($c = 1; $c <= $this->_seatingChart->getColumns(); $c++) {
            $str .= "|" . $c;
        }
        $str .= "|\r\n";
        for ($r = 1; $r <= $this->_seatingChart->getRows(); $r++) { 
            $str .= "|" . $r;
            // Remember to adjust the indexes here ince the reservation format starts with 1, 
            // and arrays start with 0
            foreach ($seatingChartData[($r-1)] as $col => $seatInfo) {
                if (empty($seatInfo[$type])) {
                    $str .= "| ";
                }
                else {
                    $str .= "|" . $seatInfo[$type];
                }
                // Handle longer spaces for numbers greater than 9, add an extra blank space
                if ($col > 8) {
                    $str .= " ";
                }
            }
            $str .= "|\r\n";
        }
        return $str;
    }
}


$start = microtime(true);
$initalMemStr = "Initial Mem Usage: ". round(memory_get_usage() / 1024) . "KB\r\n";
$initialPeakMemStr = "Initial Peak Mem Usage: ". round(memory_get_peak_usage() / 1024) . "KB\r\n";

// Get the input, instantiate the class and run it
$input = "";
while($f = fgets(STDIN)){
    $input .= $f;
}

try {
    $driver = new ReservationDriver($input, false);
    $driver->runSeatingChart(3, 11,'R1C6');
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


