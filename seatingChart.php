<?php

/**
 * Data structure to read / write reservations for a seating chart program
 *
 * @author Nick Michaels <nickmichaels@gmail.com>
 */
class SeatingChart {

    /**
     * Number of rows that the SeatingChart should have
     * i.e. how many rows of seats are in the audience
     *
     * @var int
     */
    protected $_rows;

    /**
     * Number of columns that the SeatingChart should have
     * i.e. how many seats are in each row
     *
     * @var int
     */
    protected $_columns;

    /**
     * The basic data structure for the seat reservations
     *
     * @var SplFixedArray
     */
    protected $_data;

    /**
     * Row index of the best seat location
     *
     * @var int
     */ 
    protected $_bestRow;

    /**
     * Column index of the best seat location
     *
     * @var int
     */ 
    protected $_bestCol;

    /**
     * How many individual seats (consecutive or otherwise) are available to reserve
     *
     * @var int
     */ 
    protected $_seatsAvailable;

    /**
     * Create a new SeatingChart
     *
     * In addition to assigning passed in variables, this
     * creates the data set and 
     *
     * @param int $rows
     * @param int $columns
     * @param string $bestSeat
     * @return void
     */
    public function __construct($rows, $columns, $bestSeat) {
        // Assign properties here
        $this->_rows = $rows;
        $this->_columns = $columns;
        $bestSeatInd = self::parseBestSeat($bestSeat);
        $this->_bestRow = $bestSeatInd['bestRow'];
        $this->_bestCol = $bestSeatInd['bestCol'];

        // Initialize the data set and seats available
        $this->_data = $this->initializeData();
        $this->_seatsAvailable = $rows * $columns;
    }

    public function getRows() {
        return $this->_rows;
    }

    public function getColumns() {
        return $this->_columns;
    }

    public function getData() {
        return $this->_data;
    }

    public function getSeatsAvailable() {
        return $this->_seatsAvailable;
    }

    /**
     * Initialize the data set
     *
     * This creates the data set and calculates the 
     * manhattan distance from the best seat for each 
     * seat location. It is able to do this by parsing
     * the bestSeat string into two indexes and calling 
     * the calculateManhattan method to find that distance
     *
     * @param void
     * @return SplFixedArray
     */
    public function initializeData() {
        $rowData = new SplFixedArray($this->_rows);
        foreach ($rowData as $rIndex => $row) {
            $rowData[$rIndex] = new SplFixedArray($this->_columns);
            for ($cIndex = 0; $cIndex < $this->_columns; $cIndex++) {
                // Calculate the manhattan distance for each seat now
                $mnhtn = $this->calculateManhattan($rIndex, $cIndex);

                // Special case for "best seat" being not initially reserved

                $rowData[$rIndex][$cIndex] = array(
                    'value' => NULL,
                    'manhattan' => strval($mnhtn),
                );
            }
        }

        return $rowData;
    }

    /**
     * Turn the best seat string into two indexes and store them as class properties
     *
     * @param string
     * @return array
     */
    public static function parseBestSeat($bestSeat) {
        // account for arrays starting with 0 and these requests starting with 1
        $arr = preg_split('/[RC]/', $bestSeat, null, PREG_SPLIT_NO_EMPTY);

        return array(
            'bestRow' => intval($arr[0] -1),
            'bestCol' => intval($arr[1] -1),
        );
    }

    /**
     * Calculate the manhattan distance of coordinates $row, $col from
     * the "best seat in the house" aka coordinates $this->_bestRow, $this->_bestCol
     *
     * This calculates Manhanttan distance between two points
     * (x1, y1) and (x2, y2) by using the following formula:
     * absolute value of (x1-x2) + absolute value of (y1-y2)
     *
     * @param int $row
     * @param int $col
     * @return int 
     */
    public function calculateManhattan($row, $col) {
        return intval(abs($this->_bestRow - $row) + abs($this->_bestCol - $col));
    }

    /**
     * Parse, validate and reserve an array of initial seat reservations
     *
     * This loops through each initial reservation and parses
     * the string reservation request into a set of two coordinates,
     * does some validation and then reserves the seats and marks them
     * as 'x' - this helps with visual representation after our script has run
     *
     * @param array $initRes
     * @return mixed 
     */
    public function handleInitialReservations($initRes) {
        // Each array value should be in the form of R1C1
        foreach ($initRes as $index => $res) {
            // Use a regex that ignores 'R' and 'C' to get the row and column indexes
            // This assumes the that format is RXCY, if that were to flip, the indexes would be wrong
            $arr = preg_split('/[RC]/', $res, null, PREG_SPLIT_NO_EMPTY);

            // Since the reservation format starts with 1, and arrays start with 0
            // we need to adjust these indexes
            $rIndex = intval($arr[0] -1);
            $cIndex = intval($arr[1] -1);

            // Sanity check that they exist within the bounds of the defined rows and columns
            if ( ($rIndex >= $this->_rows) || ($cIndex >= $this->_columns) ) {
                return "Initial reservation at position " . $index . " is out of bounds. Aborting program. \r\n";
            }

            // Now let's try to mark them
            if ($this->markReserved($rIndex, $cIndex, TRUE) === FALSE) {
                return "Initial reservations contained duplicates at position " . $index . ". Aborting program. \r\n";
            }
        }

        // If we got through the loop ok, then initial reservations were processed, so return true, 
        // since we don't want output unless it's one of the above catastrophic errors
        return true;
    }

    /**
     * Reserve an individual seat in the seating chart data set
     *
     * This will check to see if the seat has been reserved already, and if not
     * will mark it as reserved, either with a 'x' (denoting an initial reservation)
     * or an 'o' (denoting a secondary or "best available" reservation)
     *
     * @param array $rIndex
     * @param array $cIndex
     * @param bool $initial
     * @return mixed 
     */
    public function markReserved($rIndex, $cIndex, $initial = FALSE) {
        // Check to see that the seat isn't already reserved
        if ($this->isReserved($rIndex, $cIndex) ) {
            return false;
        }
        else {
            // Initial reservation
            if ($initial) {
                $saveValue = 'x';
            }
            // Best available
            else {
                $saveValue = 'o';
            }
            // If we dont grab it first and then re-store
            // we get the error described here 
            // https://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
            // it is slightly memory inefficient to do this but with compared
            // to using a normal php array for the data set, overall the program is faster
            // using SplFixedArray instead
            $change = $this->_data[$rIndex][$cIndex];
            $change['value'] = $saveValue;
            $this->_data[$rIndex][$cIndex] = $change;

            // Decrement the seatsAvailable
            $this->_seatsAvailable--;

            return true;
        }
    }

    /**
     * Check the dataset for whether a seat is reserved yet
     *
     * @param array $rIndex
     * @param array $cIndex
     * @return bool 
     */
    public function isReserved($rIndex, $cIndex) {
        // Check the data structure at index, index ['value']
        //if (in_array($this->_data[$rIndex][$cIndex]['value'], array('x', 'o'))) {
        // Changing from ^ to V cut some time off of execution
        if (!empty($this->_data[$rIndex][$cIndex]['value'])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Parse, validate and reserve an array of secondary / "best available" seat reservations
     *
     * This takes in an array of integers representing individual requests of X consecutive
     * "best available" seats, calls findBestSeat to find the best option (based on manhattan distance),
     * reserves the seats and outputs a string denoting the reservations made
     *
     * @param array $newRes
     * @return array 
     */
    public function handleSecondaryReservations($newRes) {
        $outArr = array();
        foreach ($newRes as $numSeats) {
            $limit = $numSeats;
            // If the best seat is still available, we want to calculate the 
            // best manhattan distance a little differently
            if ($this->isReserved($this->_bestRow, $this->_bestCol) === false) {
                $limit = $numSeats -1;
            }

            // In an effort to not waste memory, we can do some quick math to find
            // the lowest manhattan distance for a given number of seats.
            // e.g. $numSeats = 4 ... 1+2+3+4 = 10
            $bestScore = 0;
            for ($x = 0; $x < $limit; $x++) {
                $bestScore = $bestScore + ($x+1);
            }

            $seats = $this->findBestSeat($numSeats, $bestScore);
            if (is_array($seats)) {
                // Parse the array and assign the seats
                for ($i = $seats['cStart']; $i < ($seats['numSeats'] + $seats['cStart']); $i++) {
                    $this->markReserved($seats['rIndex'], $i);
                }
                $outStr = "R" . ($seats['rIndex'] + 1) . "C" . ($seats['cStart'] + 1);
                // For single seat reservations we only want "RXCY" not "RXCY - RXCY"
                // so only include the dash and more if there is more than 1 seat
                // being requested
                if ($seats['numSeats'] > 1) {
                    $outStr .= " - " . "R" . ($seats['rIndex'] + 1) . "C" . ($seats['cStart'] + $seats['numSeats']);
                }
                $outArr[] = $outStr;

            }
            else {
                $outArr[] = $seats;
            }
        }

        return $outArr;
    }

    /**
     * Do the heavy lifting of finding the best cluster of consecutive seats.
     * 
     * For this method, "best" means the lowest sum of manhattan distance for 
     * all seats in the reservation from the previously defined "best seat in
     * the house". 
     *
     * @param int $numSeats
     * @return array 
     */
    public function findBestSeat($numSeats, $bestScore) {
        // We don't handle seat requests higher than 10
        if ($numSeats > 10) {
            return 'Too many seats requested. 10 is the limit';
        }

        // We can't handle seat requests for consectuive seats larger than the total
        // amount of columns aka the total number of seats in any given row
        if ($numSeats > $this->_columns) {
            return 'Too many seats requested. Limit exceeds number of seats available in any given row';
        }

        // Barring some oddly shaped theatres where non front rows
        // are preferred, a logical place to start looking for the 
        // lowest sum of manhattan distances is in the first row
        for ($rIndex = 0; $rIndex < $this->_rows; $rIndex++) {
            for ($cIndex = 0; $cIndex < $this->_columns; $cIndex++) {
                // Sets up some temporary vars for each attempt at an option
                $seatsInCluster = 0;
                $startingIndex = NULL;
                $mnhtnValue = 0;

                // If the seat isn't reserved, let's pry a little deeper
                if (!$this->isReserved($rIndex, $cIndex) ) {
                    // Yay! we have our first seat in this "cluster"
                    $seatsInCluster++;

                    $mnhtnValue = $this->getManhattanValue($rIndex, $cIndex);

                    // This is the same logic as down below, and it would be 
                    // nice to fxnalize it, but it resets local vars too and 
                    // I don't wanna pass all that stuff by reference 
                    // This logic is really only here for cases where we have 
                    // only one seat in a request
                    if ($seatsInCluster === $numSeats) {
                        // This is an option, put it into an array
                        $optArr = array(
                            'rIndex' => $rIndex, 
                            'cStart' => $cIndex,
                            'numSeats' => $numSeats,
                        );

                        // This is the best possible option based on manhattan distance
                        // so return it
                        if ($mnhtnValue <= $bestScore) {
                            return $optArr;
                        }
                        // Store this option
                        $options[$mnhtnValue] = $optArr;
                        
                        // Reset all of these for the next option
                        $startingIndex = NULL;
                        $mnhtnValue = 0;
                        $optArr = NULL;
                        continue;
                    }

                    // So we have an initial seat that works, and we need more than one seat
                    // in this reservation so let's delve a bit deeper
                    $startingIndex = $cIndex;
                    for ($s = 1; $s <= $numSeats; $s++) {
                        // Don't check out of range values 
                        if ( ($startingIndex+$s) === $this->_columns) {
                            // Move on to the next row
                            continue 2;
                        }

                        // If the seat isn't reserved, let's pry a little deeper
                        if (!$this->isReserved($rIndex, ($startingIndex+$s) )) {
                            $seatsInCluster++;
                            // So many manhattans
                            $mnhtnValue = $mnhtnValue + $this->getManhattanValue($rIndex, ($startingIndex+$s));
                            // If we have reached the number of seats that we need, let's save this option
                            if ($seatsInCluster === $numSeats) {
                                // This is an option, put it into an array
                                $optArr = array(
                                    'rIndex' => $rIndex, 
                                    'cStart' => $startingIndex,
                                    'numSeats' => $numSeats,
                                );

                                // This is the best possible option based on manhattan distance
                                // so return it
                                if ($mnhtnValue <= $bestScore) {
                                    return $optArr;
                                }
                                // Store this option
                                $options[$mnhtnValue] = $optArr;
                                
                                // Reset all of these for the next option
                                $startingIndex = NULL;
                                $mnhtnValue = 0;
                                $optArr = NULL;
                                continue 2;
                            }

                        }
                        else {
                            continue 2;
                        }
                    }
                }
                // If it was reserved, go to the next one
                else {
                    continue;
                }
                
            }

        }
        // If there are no options, we can't reserve that number of seats
        if (empty($options)) {
            return 'Not Available';
        }
        // Otherwise sort the array of options by its keys and grab
        // the first result to get the option with the lowest Manhattan distance
        else {
            ksort($options);
            return array_shift($options);
        }
    }

    /**
     * Look into the data set at $rIndex, $cIndex and return the previously calculated
     * Manhattan "value" aka Manhattan distance
     *
     * @param array $rIndex
     * @param array $cIndex
     * @return int 
     */
    public function getManhattanValue($rIndex, $cIndex) {
        return $this->_data[$rIndex][$cIndex]['manhattan'];
    }

}

?>