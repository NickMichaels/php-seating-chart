<?php
use PHPUnit\Framework\TestCase;

final class SeatingChartTest extends TestCase {

    /**
     * Initial setup of properties / test data and expected outputs
     */
    public function setUp() {
        $this->_goodInitRes = array('R1C4', 'R1C6', 'R2C3', 'R2C7', 'R3C9', 'R3C10');
        $this->_badInitRes = array('R1C4', 'R1C6', 'R2C3', 'R2C7', 'R3C9', 'R3C12');
        $this->_dupeInitRes = array('R1C4', 'R1C6', 'R2C3', 'R2C7', 'R3C9', 'R3C9');
        $this->_goodSecRes = array(3, 3, 3, 1, 10);
        $this->_goodSecOut = array(
			'R1C7 - R1C9',
		    'R2C4 - R2C6',
		    'R3C5 - R3C7',
		    'R1C5',
		    'Not Available',
    	);
    	$this->_altSecRes = array(12, 5, 4, 4, 10, 9, 8);
    	$this->_altSecOut = array (
    		'Too many seats requested. 10 is the limit',
		    'R1C7 - R1C11',
		    'R3C5 - R3C8',
		    'R2C8 - R2C11',
		    'Not Available',
		    'Not Available',
		    'Not Available',
		);
   	}

    /**
     * Test that we can create a new object and it is an instance of the proper class
     */
    public function testCanBeInstatiated() {
        $seat = new SeatingChart(3, 11, 'R1C6');
        $this->assertInstanceOf(SeatingChart::class, $seat);
    }

    /**
     * Test that when we get an exception when we attempt to create 
     * an instance with an invalid number of rows
     *
     * @expectedException Exception
     */
    public function testInstantiateBadRows() { 
        $seat = new SeatingChart(0, 11, 'R1C6');
    }

    /**
     * Test that when we get an exception when we attempt to create 
     * an instance with an invalid number of columns
     *
     * @expectedException Exception
     */
    public function testInstantiateBadCols() { 
        $seat = new SeatingChart(3, 0, 'R1C6');
    }

    /**
     * Test that when we get an exception when we attempt to create 
     * an instance with an invalid "best seat" string
     *
     * @expectedException Exception
     */
    public function testInstantiateBadBestSeat() { 
        $seat = new SeatingChart(3, 0, 'R1C12');
    }

    /**
     * Test that when we process a set of known valid
     * initial reservation data, that we actually mark
     * the requested seats as reserved.
     */
    public function testHandleInitialReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_goodInitRes);

    	$data = $seat->getData();
    	// Programmatically check and actually use parseBestSeat to check the 
    	// data set at rIndex, cIndex
    	foreach ($this->_goodInitRes as $res) {
    		$seatInd = SeatingChart::parseBestSeat($res);
            $rIndex = $seatInd['bestRow'];
            $cIndex = $seatInd['bestCol'];

            $this->assertEquals('x', $data[$rIndex][$cIndex]['value']);
    	}
    }

    /**
     * Test we get an exception when we pass out of bounds 
     * initial reservation data
     *
     * @expectedException Exception
     */
    public function testBadInitialReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_badInitRes);
    }

    /**
     * Test we get an exception when we pass duplicate
     * initial reservation data
     *
     * @expectedException Exception
     */
    public function testDupeInitialReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_dupeInitRes);
    }

    /**
     * Test we get the expected output and number of seats 
     * remaining when processing secondary reservations
     * with two separate sets of reservations
     */
    public function testHandleSecondaryReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_goodInitRes);
    	$outArr = $seat->handleSecondaryReservations($this->_goodSecRes);
    	// Check the output array against expected
    	$this->assertEquals($this->_goodSecOut, $outArr);
    	// We know that there should only be 17 left
    	$this->assertEquals(17, $seat->getSeatsAvailable());

    	// Let's try a different set of secondary reservations
    	$seat1 = new SeatingChart(3, 11, 'R1C6');
    	$seat1->handleInitialReservations($this->_goodInitRes);
    	$outArr1 = $seat1->handleSecondaryReservations($this->_altSecRes);
    	// Check the output array against expected
    	$this->assertEquals($this->_altSecOut, $outArr1);
    	// We know that there should only be 14 left
    	$this->assertEquals(14, $seat1->getSeatsAvailable());
    }

    /**
     * Test that the number of seats available is being
     * decremented appropriately when we process reservations
     */
    public function testGetSeatsAvailable() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_goodInitRes);
    	$this->assertEquals(27, $seat->getSeatsAvailable());

    	$seat->handleSecondaryReservations(array(8));
    	$this->assertEquals(19, $seat->getSeatsAvailable());

    	$seat->handleSecondaryReservations(array(2));
    	$this->assertEquals(17, $seat->getSeatsAvailable());
    }

}
