<?php
require_once(dirname(__FILE__).'/../core/lib/xmlToArray.php');

class Unit_XmlToArrayTest extends PHPUnit_Framework_TestCase {
	
	private $_maxDepth = 20;
	
	private $_xml = <<<EOD
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<billcode_request>
   <billcode>test</billcode>
</billcode_request>
EOD;
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('XmlToArray');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	protected static function getMethod($name) {
		$class = new ReflectionClass('XmlToArray');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	
	public function testConstructNoValidInputException () {
		$this->setExpectedException('XmlToArrayException');
		
		$XmlToArray = new XmlToArray(12);
		$this->assertTrue($XmlToArray instanceof XmlToArrayException );
	}
	
	
	public function testConstruct () {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$this->assertAttributeEquals(
				$this->_maxDepth,  /* expected value */
				'_maxDepth',  /* attribute name */
				$XmlToArray /* object         */
		);
	}
	
	
	public function testLog () {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$msg = 'text';
		$this->assertFalse($XmlToArray->log($msg));
		
		if (!class_exists('Object')) {
			require_once('SofortObject.php');
			
			$msg = 'test';
			$this->assertEquals(array($msg, 2), $XmlToArray->log($msg));
			
			$type = 1;
			$this->assertEquals(array($msg, $type), $XmlToArray->log($msg, $type));
		}
	}
	
	
	public function testToArray() {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$billcodeArray = array(
							'billcode_request' => array(
										'billcode' => array(
													'@data' => 'test',
													'@attributes' => array()
												),
										'@data' => '',
										'@attributes' => array()));
		
		$this->assertEquals($XmlToArray->toArray(), $billcodeArray);
	}
	
	
	public function testRender() {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$billcodeArray = array(
							'billcode_request' => array(
										'billcode' => array(
													'@data' => 'test',
													'@attributes' => array()
												),
										'@data' => '',
										'@attributes' => array()));
		
		$this->assertEquals($XmlToArray->render($this->_xml), $billcodeArray);
	}
	
	
	public function testContents() {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$contents = self::getMethod('_contents');
		
		$contents->invoke($XmlToArray, 'test', 'test');
	}
	
	
	public function testDefault() {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$default = self::getMethod('_default');
		
		$default->invoke($XmlToArray, 'test', 'test');
		
		$html_entities = get_html_translation_table(HTML_ENTITIES);
		$default->invoke($XmlToArray, 'test', $html_entities['>']);
		$default->invoke($XmlToArray, 'test', '&euro;');
		
		putenv('sofortDebug=true');
		
		$this->setExpectedException('XmlToArrayException', 'Unknown error occurred');
		
		$this->assertTrue(@$default->invoke($XmlToArray, 'test', 'test') instanceof XmlToArrayException);
	}
	
	
 	public function testDefaultTriggerError () {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$default = self::getMethod('_default');
		
		putenv('sofortDebug=true');
		
		try {
			$default->invoke($XmlToArray, 'test', 'test');
		}
		catch (Exception $expected) {
			return;
		}
		
		$this->fail('An expected exception has not been raised.');
		
		//$this->assertTrue($default->invoke($XmlToArray, 'test', 'test') instanceof E_USER_WARNING );
	}
	
	
	public function testEnd() {
		$XmlToArray = new XmlToArray($this->_xml, $this->_maxDepth);
		
		$XmlToArrayNode = new XmlToArrayNode('test', array('test' => 'test'));
		$XmlToArrayNode->setParentXmlToArrayNode($XmlToArrayNode);
		
		$end = self::getMethod('_end');
		$CurrentXmlToArrayNode = self::getProperty('_CurrentXmlToArrayNode');
		$CurrentXmlToArrayNode->setValue($XmlToArray, $XmlToArrayNode);
		
		$XMLParser = xml_parser_create();
		
		try {
			$end->invoke($XmlToArray, $XMLParser, 'test1');
		}
		catch (Exception $expected) {
			return;
		}
		
		$this->fail('An expected exception has not been raised.');
		
		xml_parser_free($XMLParser);
	}
	
	public function testStart() {
		$XmlToArray = new XmlToArray($this->_xml, 20);
		
		$XMLParser = xml_parser_create();
		$start = self::getMethod('_start');
		
		$start->invoke($XmlToArray, $XMLParser, 'test1', array());
		
		$XmlToArrayNode = new XmlToArrayNode('test', array('test' => 'test'));
		
		$CurrentXmlToArrayNode = self::getProperty('_CurrentXmlToArrayNode');
		$CurrentXmlToArrayNode->setValue($XmlToArray, $XmlToArrayNode);
		
		
		$this->assertAttributeEquals(
				$XmlToArrayNode,  /* expected value */
				'_CurrentXmlToArrayNode',  /* attribute name */
				$XmlToArray /* object         */
		);
	}
	
	/**
	 * @expectedException XmlToArrayException
	 * @expectedExceptionMessage Parse Error: max depth exceeded.
	 */
	public function testStartException() {
		$XmlToArray = new XmlToArray($this->_xml, 1);
		
		$XMLParser = xml_parser_create();
		$start = self::getMethod('_start');
		
		$this->assertTrue($start->invoke($XmlToArray, $XMLParser, 'test1', array()) instanceof XmlToArrayException);
	}
}