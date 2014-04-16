<?php
require_once(dirname(__FILE__).'/../core/lib/arrayToXml.php');

class Unit_ArrayToXmlTest extends PHPUnit_Framework_TestCase {
	
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('ArrayToXml');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	protected static function getMethod($name) {
		$class = new ReflectionClass('ArrayToXml');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	
	public function testConstuctInputSizeException () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(1,2));
	}
	
	
	public function testConstuctMaxDepthException () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(1), 55);
	}
	
	
	public function testConstruct () {
		$ArrayToXml = new ArrayToXml(array());
		
		$this->assertAttributeEquals('', '_parsedData', $ArrayToXml);
		
		$ArrayToXml = new ArrayToXml(array(), 5, false);
		
		$this->assertAttributeEquals(5, '_maxDepth', $ArrayToXml);
	}
	
	public function testToXml () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$parsedData = self::getProperty('_parsedData');
		
		$test = 'Test';
		
		$parsedData->setValue($ArrayToXml, $test);
		
		$this->assertEquals($test, $ArrayToXml->toXml(false, false));
		$this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\nTest", $ArrayToXml->toXml());
	}
	
	
	public function testRender () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<test>1</test>", $ArrayToXml->render(array('test' => 1)));
	}
	
	
	public function testCheckDepth () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$checkDepth = self::getMethod('_checkDepth');
		$checkDepth->invoke($ArrayToXml, array(11));
		
	}
	
	
	public function testCreateNode () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$SofortTag = new SofortTag('node', array('attribute1' => 1), array());
		
		$createNode = self::getMethod('_createNode');
		$this->assertEquals($createNode->invoke($ArrayToXml, 'node', array('attribute1' => 1), array()), $SofortTag);
	}
	
	
	public function testCreateTextNode () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$SofortText = new SofortText('node', true, false);
		
		$createTextNode = self::getMethod('_createTextNode');
		$this->assertEquals($createTextNode->invoke($ArrayToXml, 'node', false), $SofortText);
	}
	
	
	public function testExtractAttributesSection () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$extractAttributesSection = self::getMethod('_extractAttributesSection');
		
		$node = array('@attributes' => 'test');
		$attributes = array('test');
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
		
		$node = array('@attributes' => array('test'));
		$attributes = array('test');
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
		
		$node = array('@attributes' => false);
		$attributes = array();
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
	}
	
	
	public function testExtractDataSection () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$extractDataSection = self::getMethod('_extractDataSection');
		
		$SofortText = new SofortText('node', true, false);
		
		$node = array('@data' => 'node');
		$this->assertEquals($extractDataSection->invoke($ArrayToXml, &$node, true), array($SofortText));
		
		$node = array('@data' => false);
		$data = array();
		$this->assertEquals($extractDataSection->invoke($ArrayToXml, &$node, true), $data);
	}
	
	
	public function testPrivateRender () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		
		$render = self::getMethod('_render');
		
		$SofortTag = new SofortTag('node', array('attribute1' => 1), array());
		
		$render->invoke($ArrayToXml, array('test'), $SofortTag, 5, true);
		$render->invoke($ArrayToXml, 'test', $SofortTag, 5, true);
	}
}