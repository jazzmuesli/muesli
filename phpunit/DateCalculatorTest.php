<?php
interface RepeatType {
//TODO: one day PHP gets enum with typisation..
	const DAILY = 1;
	const WEEKLY = 2;
	const MONTHLY = 3;
}

interface DateCalculator {
	public function getNextDate($date, $repeat_type);
	public function getNextDateWithStartDate($date, $repeat_type, $start);
}
abstract class AbstractDateCalculator implements DateCalculator {
	public function formatAsYMD($month, $day, $year) {
		return $this->formatUnixTime(mktime(0, 0, 0, $month, $day, $year));
	}
	private function formatUnixTime($unixtime) {
		return date("Y-m-d", $unixtime);
	}
}

class OldDateCalculator extends AbstractDateCalculator {
	function __toString() {
		return "Old getNextDate() implementation";
	}

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
	if ($return_date && !$start) {
		return $return_date;
	}
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

class NewDateCalculator extends AbstractDateCalculator {
	function getNextDateWithStartDate($date, $repeat_type, $start) {
		$utime = strtotime($date);
		$dateDay = date("d", $utime);

		$stime = strtotime($start);
		$startMonth = date("m",$stime);
		$startDay = date("d", $stime);
		$startYear = date("Y", $stime);
		switch ($repeat_type) {
			case RepeatType::DAILY:
				// if it's a daily event it can start on the specified date
				return $start;
			case RepeatType::WEEKLY:
				// for weekly events:
				// get the weekday of the event start date and the weekday of the original event day
				$eventWday = date("N",strtotime($date));
				$afterWday = date("N",strtotime($start));
				// get the difference
				$addDays = $eventWday - $afterWday;
				// and add 7 days if it's negative
				if ($addDays < 0) {
					$addDays += 7;
				}
				// add the number of days to get to the wanted weekday
				return $this->formatAsYMD($startMonth, $startDay+$addDays, $startYear);
				break;
			case RepeatType::MONTHLY:
				// if it's a monthly date just add the month and year of the specific start date
				return $this->formatAsYMD($startMonth, $dateDay, $startYear);
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
			case RepeatType::DAILY:
				return $this->formatAsYMD($dateMonth, $dateDay+1, $dateYear);
			case RepeatType::WEEKLY:
				return $this->formatAsYMD($dateMonth, $dateDay+7, $dateYear);
			case RepeatType::MONTHLY:
				return $this->formatAsYMD($dateMonth+1, $dateDay, $dateYear);
			default:
				throw new Exception("Unknown repeat_type: $repeat_type");
		}
	}

	function __toString() {
		return "New getNextDate() implementation";
	}
}

abstract class DateCalculatorTst extends PHPUnit_Framework_TestCase {

	protected $calculator;
	function __construct($calc) {
		$this->calculator = $calc;
		print "Using calculator: " . $calc . "\n";
	}

        public function test1NovemberGetNextDate() {
                $date = $this->calculator->formatAsYMD(11, 1, 2012); // first November 2012
                $this->assertEquals("2012-11-02", $this->calculator->getNextDate($date, RepeatType::DAILY));
                $this->assertEquals("2012-11-08", $this->calculator->getNextDate($date, RepeatType::WEEKLY));
		$this->assertEquals("2012-12-01", $this->calculator->getNextDate($date, RepeatType::MONTHLY));
        }

        public function test1NovemberGetNextWithStartDate() {
                $date = $this->calculator->formatAsYMD(11, 1, 2012); // first November 2012
         	$this->assertEquals("2012-12-03", $this->calculator->getNextDateWithStartDate($date, RepeatType::DAILY, "2012-12-03"));
                $this->assertEquals("2032-12-09", $this->calculator->getNextDateWithStartDate($date, RepeatType::WEEKLY, "2032-12-07"));
		$this->assertEquals("2012-12-01", $this->calculator->getNextDateWithStartDate($date, RepeatType::MONTHLY, "2012-12-03"));
	}

        public function test30NovemberGetNextDate() {
                $date = $this->calculator->formatAsYMD(11, 30, 2012); // 30. November 2012
         	$this->assertEquals("2012-12-01", $this->calculator->getNextDate($date, RepeatType::DAILY));
                $this->assertEquals("2012-12-07", $this->calculator->getNextDate($date, RepeatType::WEEKLY));
		$this->assertEquals("2012-12-30", $this->calculator->getNextDate($date, RepeatType::MONTHLY));
	}

        public function test30NovemberGetNextWithStartDate() {
                $date = $this->calculator->formatAsYMD(11, 30, 2012); // 30. November 2012
         	$this->assertEquals("2012-12-03", $this->calculator->getNextDateWithStartDate($date, RepeatType::DAILY, "2012-12-03"));
                $this->assertEquals("2032-12-10", $this->calculator->getNextDateWithStartDate($date, RepeatType::WEEKLY, "2032-12-07"));
		$this->assertEquals("2012-12-30", $this->calculator->getNextDateWithStartDate($date, RepeatType::MONTHLY, "2012-12-03"));
	}

	/**
	* To cover addDays < 0
	*/
        public function testMondayNovemberGetNextWithStartDate() {
                $date = $this->calculator->formatAsYMD(11, 26, 2012);
                $this->assertEquals("2032-12-13", $this->calculator->getNextDateWithStartDate($date, RepeatType::WEEKLY, "2032-12-07"));
	}
}
?>
