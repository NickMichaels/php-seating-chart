<?php
use PHPUnit\Framework\TestCase;

final class SeatingChartTest extends TestCase {

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

    public function testCanBeInstatiated() {
        $seat = new SeatingChart(3, 11, 'R1C6');
        $this->assertInstanceOf(SeatingChart::class, $seat);
    }

    /**
     * @expectedException Exception
     */
    public function testInstantiateBadRows() { 
        $seat = new SeatingChart(0, 11, 'R1C6');
    }

    /**
     * @expectedException Exception
     */
    public function testInstantiateBadCols() { 
        $seat = new SeatingChart(3, 0, 'R1C6');
    }

    /**
     * @expectedException Exception
     */
    public function testInstantiateBadBestSeat() { 
        $seat = new SeatingChart(3, 0, 'R1C12');
    }

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
     * @expectedException Exception
     */
    public function testBadInitialReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_badInitRes);
    }

    /**
     * @expectedException Exception
     */
    public function testDupeInitialReservations() {
    	$seat = new SeatingChart(3, 11, 'R1C6');
    	$seat->handleInitialReservations($this->_dupeInitRes);
    }

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
