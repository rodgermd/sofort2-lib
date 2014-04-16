<?php

require_once(dirname(__FILE__).'/../core/abstractLoggerHandler.php');

class Unit_AbstractLoggerHandlerTest extends PHPUnit_Framework_TestCase {


	public function testConstuct () {
		$AbstractLoggerHandler = $this->getMockForAbstractClass('AbstractLoggerHandler');
	}
}