<?php
require_once(dirname(__FILE__).'/../core/fileLogger.php');

class Unit_FileLoggerTest extends PHPUnit_Framework_TestCase {
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('fileLogger');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	public function testConstruct() {
		$SofortLibLogger = new fileLogger();
		
		$this->assertAttributeEquals(
				 realpath ( dirname(__FILE__) . '/../core') .  '/logs/log.txt',  /* expected value */
				'_logfilePath',  /* attribute name */
				$SofortLibLogger /* object         */
		);
	}
	
	public function testSetLogfilePath() {
		$SofortLibLogger = new fileLogger('wusel');
		
		$this->assertAttributeEquals(
				'wusel',  /* expected value */
				'_logfilePath',  /* attribute name */
				$SofortLibLogger /* object         */
		);
		
		$SofortLibLogger->setLogfilePath('test');
		
		$this->assertAttributeEquals(
				'test',  /* expected value */
				'_logfilePath',  /* attribute name */
				$SofortLibLogger /* object         */
		);
	}
	
	
	public function testLog() {
		$stub = $this->getMock('fileLogger', array('_log'));
		
		$stub->expects($this->at(0))
				->method('_log')
				->with('log')
				->will($this->returnValue('log'));
		
		$this->assertEquals('log', $stub->log('log'));

		$stub->expects($this->at(0))
				->method('_log')
				->with('error')
				->will($this->returnValue('error'));
		
		$this->assertEquals('error', $stub->log('error'));
		
		$stub->expects($this->at(0))
				->method('_log')
				->with('warning')
				->will($this->returnValue('warning'));
		
		$this->assertEquals('warning', $stub->log('warning'));
	}
	
	
	public function testLogWriting() {
		$SofortLibLogger = new fileLogger();
		
		$this->assertTrue($SofortLibLogger->log('test', 'log'));
	}


	public function testLogRotate() {
		$SofortLibLogger = new fileLogger();
		$SofortLibLogger->maxFilesize = 1;
		$SofortLibLogger->log('Aged, tangy pudding is best whisked with hot cream.What’s the secret to a sour and shredded cauliflower? Always use crushed vodka.');
		$SofortLibLogger->log('Brush the tuna with sticky garlic, szechuan pepper, dill, and butterscotch making sure to cover all of it.');
		$SofortLibLogger->log('Caviar pudding has to have a tasty, whole rice component.');
		$SofortLibLogger->log('Try marinating the milk garlics with nutty condensed milk and bourbon, refrigerated.');
		$SofortLibLogger->log('Shrimps taste best with anchovy essence and lots of nutmeg.');
		$SofortLibLogger->log('Soak one package of cabbage in one cup of joghurt.');
		$SofortLibLogger->log('Clammy, quartered pudding is best mixed with divided salsa verde.');

	}
}