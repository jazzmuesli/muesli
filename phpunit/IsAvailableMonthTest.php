<?php

	
// new: checks if the month/year is within nearest 4 month from now
function isAvailableMonth($m,$y, $time=-1) {
  if ($time < 0) {
	$time = time();
  }
  if ($m <0 || $m >12 || $y < 0 || $y > PHP_INT_MAX) {
	return false;// Invalid
  }
// We consider only months and one year has only 12 months.
$nowMonths = date("Y", $time) * 12 + date("n", $time);
$curMonths = $y*12 + $m;
// nowMonths-1 <= curMonths <= nowMonths+4
return ($nowMonths-1 <= $curMonths && $curMonths <= $nowMonths+4);
/*
  $available = FALSE;
  $month = date("n", $time);
  $year = date("Y", $time);
  for ($i=-1;$i<=4;$i++) {
    $aMonth=$month+$i;
    $aYear=$year;
    if ($aMonth > 12) { $aMonth = $aMonth - 12 ; $aYear = $year + 1;}
    if ($aMonth < 1) { $aMonth = $aMonth + 12 ; $aYear = $year - 1;}
    if ( ( $m == $aMonth ) && ( $y == $aYear ) ) { $available = TRUE; }
  }
  return $available;
*/
}
class IsAvailableMonthTest extends PHPUnit_Framework_TestCase
{

	public function testNovember() {
		$fnTime = mktime(0, 0, 0, 11, 1, 2012); // first November 2012
		$this->assertFalse(isAvailableMonth(9, 2012, $fnTime));
		$this->assertTrue(isAvailableMonth(10, 2012, $fnTime));
		$this->assertTrue(isAvailableMonth(11, 2012, $fnTime));
		$this->assertTrue(isAvailableMonth(12, 2012, $fnTime));
                $this->assertFalse(isAvailableMonth(13, 2012, $fnTime));
		$this->assertTrue(isAvailableMonth(1, 2013, $fnTime));
		$this->assertTrue(isAvailableMonth(3, 2013, $fnTime));
		$this->assertFalse(isAvailableMonth(4, 2013, $fnTime));
	}
}
?>
