<?php
use PHPUnit\Framework\TestCase;

final class ReservationDriverTest extends TestCase {

    /**
     * Initial setup of properties / test data and expected outputs
     */
    public function setUp() {
        $this->_goodInput = 'R1C4 R1C6 R2C3 R2C7 R3C9 R3C10
3
3
3
1
10';

        $this->_badInitRes = 'R1C4 R1C6 R2C3 R2C7 R3C9 R3C12
3
3
3
1
10';

        $this->_dupeInitRes = 'R1C4 R1C6 R2C3 R2C7 R3C9 R3C9
3
3
3
1
10';
        $this->_goodOutput = 'R1C7 - R1C9
R2C4 - R2C6
R3C5 - R3C7
R1C5
Not Available
17';
        $this->_goodOutputWithCharts = 'R1C7 - R1C9
R2C4 - R2C6
R3C5 - R3C7
R1C5
Not Available
17
|Seating Chart|
|~|1|2|3|4|5|6|7|8|9|10|11|
|1| | | |x|o|x|o|o|o|  |  |
|2| | |x|o|o|o|x| | |  |  |
|3| | | | |o|o|o| |x|x |  |
x - Initial reservation; o - Reserved by best available selection
|Manhattan Distances|
|~|1|2|3|4|5|6|7|8|9|10|11|
|1|5|4|3|2|1| |1|2|3|4 |5 |
|2|6|5|4|3|2|1|2|3|4|5 |6 |
|3|7|6|5|4|3|2|3|4|5|6 |7 |
';
    }

    /**
     * Test that we can create a new object and it is an instance of the proper class
     */
    public function testCanBeInstantiated() {
        $reserve = new ReservationDriver($this->_goodInput, false);
        $this->assertInstanceOf(ReservationDriver::class, $reserve);
    }

    /**
     * Test that we get an exception when was pass blank input into the class
     *
     * @expectedException Exception
     */
    public function testNoInput() {
        $reserve = new ReservationDriver('', false);
        $this->assertInstanceOf(ReservationDriver::class, $reserve);
    }

    /**
     * Test that when we attempt to create a new instance
     * with out of bounds data, that we get an exception
     *
     * @expectedException Exception
     */
    public function testRunSeatingChartBadReservation() { 
        $reserve = new ReservationDriver($this->_badInitRes, false);
        $output = $reserve->runSeatingChart(3, 11, 'R1C6');
    }

    /**
     * Test that when we attempt to create a new instance
     * with duplicate data, that we get an exception
     *
     * @expectedException Exception
     */
    public function testRunSeatingChartDupeReservation() { 
        $reserve = new ReservationDriver($this->_dupeInitRes, false);
        $output = $reserve->runSeatingChart(3, 11, 'R1C6');
    }

    /**
     * Test that when we try to execute runSeatingChart
     * with an invalid number of rows, we get an exception
     *
     * @expectedException Exception
     */
    public function testRunSeatingChartBadRows() { 
        $reserve = new ReservationDriver($this->_goodInput, false);
        $output = $reserve->runSeatingChart(0, 11, 'R1C6');
    }

    /**
     * Test that when we try to execute runSeatingChart
     * with an invalid number of columns, we get an exception
     *
     * @expectedException Exception
     */
    public function testRunSeatingChartBadCols() { 
        $reserve = new ReservationDriver($this->_goodInput, false);
        $output = $reserve->runSeatingChart(3, 0, 'R1C6');
    }

    /**
     * Test that when we try to execute runSeatingChart
     * with an invalid "best seat" string, we get an exception
     *
     * @expectedException Exception
     */
    public function testRunSeatingChartBadBestSeat() { 
        $reserve = new ReservationDriver($this->_goodInput, false);
        $output = $reserve->runSeatingChart(3, 11, 'R1C12');
    }

}
