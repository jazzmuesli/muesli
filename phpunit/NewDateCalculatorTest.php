<?php
include_once 'DateCalculatorTest.php';
class NewDateCalculatorTest extends DateCalculatorTst {
	function __construct() {
		parent::__construct(new NewDateCalculator());
	}
}
?>
