<?php
interface DateCalculator {
	const ONE_DAY = 1;
	const ONE_WEEK = 2;
	const ONE_MONTH = 3;
	public function getNextDate($date, $repeat_type);
	public function getNextDateWithStartDate($date, $repeat_type, $start);
}

class OldDateCalculator {
function getNextDateWithStartDate($date, $repeat_type, $start) {
	return $this->getNextDate($date, $repeat_type, $start);
}
// helper functions
function getNextDate($date, $repeat_type, $start = false) {
	// calculate new event date
	switch ($repeat_type) {
		case 1:
			$return_date = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($date))  , date("d",strtotime($date))+1, date("Y",strtotime($date))));
			break;
		case 2:
			$return_date = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($date))  , date("d",strtotime($date))+7, date("Y",strtotime($date))));
			break;
		case 3:
			$return_date = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($date))+1  , date("d",strtotime($date)), date("Y",strtotime($date))));
			break;
	}//PR: $return_date will not be used again.
	// if the date has to start on or after a specific date:
	if ( $start ) {
		switch ($repeat_type) {
			case 1:
				// if it's a daily event it can start on the specified date
				$return_date = $start;
				break;
			case 2:
				// for weekly events:
				// get the weekday of the event start date and the weekday of the original event day
				$eventWday = date("N",strtotime($date));
				$afterWday = date("N",strtotime($start));
				// get the difference and add 7 days if it's negative
				$addDays = $eventWday - $afterWday;
				$addDays = $addDays + (( $addDays < 0 ) ? ( 7 ) : ( 0 ));
				// add the number of days to get to the wanted weekday
				$return_date = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($start))  , date("d",strtotime($start))+$addDays, date("Y",strtotime($start))));
				break;
			case 3:
				// if it's a monthly date just add the month and year of the specific start date
				$return_date = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($start))  , date("d",strtotime($date)), date("Y",strtotime($start))));
				break;
		}
	}
	return $return_date;
}

}

class NewDateCalculator implements DateCalculator {
	function getNextDateWithStartDate($date, $repeat_type, $start) {
		$utime = strtotime($date);
		$dateDay = date("d", $utime);

		$stime = strtotime($start);
		$startMonth = date("m",$stime);
		$startDay = date("d", $stime);
		$startYear = date("Y", $stime);
		switch ($repeat_type) {
			case DateCalculator::ONE_DAY:
				// if it's a daily event it can start on the specified date
				return $start;
			case DateCalculator::ONE_WEEK:
				// for weekly events:
				// get the weekday of the event start date and the weekday of the original event day
				$eventWday = date("N",strtotime($date));
				$afterWday = date("N",strtotime($start));
				// get the difference and add 7 days if it's negative
				$addDays = $eventWday - $afterWday;
				$addDays = $addDays + (( $addDays < 0 ) ? ( 7 ) : ( 0 ));
				// add the number of days to get to the wanted weekday
				$ftime = mktime(0, 0, 0, $startMonth, $startDay+$addDays, $startYear);
				break;
			case DateCalculator::ONE_MONTH:
				// if it's a monthly date just add the month and year of the specific start date
				$ftime = mktime(0, 0, 0, $startMonth, $dateDay, $startYear);
				break;
			default:
				throw new Exception("Unknown repeat_type: $repeat_type");

		}
		return $this->formatYMD($ftime);
	}
	function getNextDate($date, $repeat_type) {
		$utime = strtotime($date);
		$dateMonth = date("m",$utime);
		$dateDay = date("d", $utime);
		$dateYear = date("Y", $utime);
		switch ($repeat_type) {
			case DateCalculator::ONE_DAY:
				return $this->formatYMDasYMD($dateMonth, $dateDay+1, $dateYear);
			case DateCalculator::ONE_WEEK:
				return $this->formatYMDasYMD($dateMonth, $dateDay+7, $dateYear);
			case DateCalculator::ONE_MONTH:
				return $this->formatYMDasYMD($dateMonth+1, $dateDay, $dateYear);// Add one month
			default:
				throw new Exception("Unknown repeat_type: $repeat_type");
		}
	}

	private function formatYMDasYMD($month, $day, $year) {
		return $this->formatYMD(mktime(0, 0, 0, $month, $day, $year));
	}
	private function formatYMD($unixtime) {
		return date("Y-m-d", $unixtime);
	}
}

class DateCalculatorTest extends PHPUnit_Framework_TestCase {

	protected $calculator;
	protected function setUp() {
		$this->calculator = new OldDateCalculator();
	}
	private function formatYMD($month, $day, $year) {
	        $fnTime = mktime(0, 0, 0, $month, $day, $year);
                return date("Y-m-d", $fnTime);
	}
        public function test1NovemberGetNextDate() {
                $date = $this->formatYMD(11, 1, 2012); // first November 2012
                $this->assertEquals("2012-11-02", $this->calculator->getNextDate($date, DateCalculator::ONE_DAY));
                $this->assertEquals("2012-11-08", $this->calculator->getNextDate($date, DateCalculator::ONE_WEEK));
		$this->assertEquals("2012-12-01", $this->calculator->getNextDate($date, DateCalculator::ONE_MONTH));
        }

        public function test1NovemberGetNextWithStartDate() {
                $date = $this->formatYMD(11, 1, 2012); // first November 2012
         	$this->assertEquals("2012-12-03", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_DAY, "2012-12-03"));
                $this->assertEquals("2032-12-09", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_WEEK, "2032-12-07"));
		$this->assertEquals("2012-12-01", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_MONTH, "2012-12-03"));
	}

        public function test30NovemberGetNextWithStartDate() {
                $date = $this->formatYMD(11, 30, 2012); // 30. November 2012
         	$this->assertEquals("2012-12-03", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_DAY, "2012-12-03"));
                $this->assertEquals("2032-12-10", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_WEEK, "2032-12-07"));
		$this->assertEquals("2012-12-30", $this->calculator->getNextDateWithStartDate($date, DateCalculator::ONE_MONTH, "2012-12-03"));
	}

        public function test30NovemberGetNextDate() {
                $date = $this->formatYMD(11, 30, 2012); // 30. November 2012
         	$this->assertEquals("2012-12-03", $this->calculator->getNextDate($date, DateCalculator::ONE_DAY, "2012-12-03"));
                $this->assertEquals("2032-12-10", $this->calculator->getNextDate($date, DateCalculator::ONE_WEEK, "2032-12-07"));
		$this->assertEquals("2012-12-30", $this->calculator->getNextDate($date, DateCalculator::ONE_MONTH, "2012-12-03"));
	}

}

?>
