<?php
require_once(dirname(__FILE__).'/../core/abstractDataHandler.php');
require_once('sofortLibTest.php');

class Unit_AbstractDataHandlerTest extends SofortLibTest {
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('AbstractDataHandler');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	/**
	 *
	 * @dataProvider getUserIdProvider
	 */
	public function testGetUserId ($provided) {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$this->assertEquals(self::$user_id, $AbstractDataHandler->getUserId());
		$AbstractDataHandler->setUserId($provided);
		$this->assertEquals($provided, $AbstractDataHandler->getUserId());
	}
	
	
	public function getUserIdProvider() {
		return array(
					array(4711,),
					array(20,),
				);
	}
	
	
	/**
	 *
	 * @dataProvider getProjectIdProvider
	 */
	public function testGetProjectId ($provided) {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$this->assertEquals(self::$project_id, $AbstractDataHandler->getProjectId());
		$AbstractDataHandler->setProjectId($provided);
		$this->assertEquals($provided, $AbstractDataHandler->getProjectId());
	}
	
	
	public function getProjectIdProvider() {
		return array(
					array(4711,),
					array(20,),
				);
	}
	
	
	/**
	 *
	 * @dataProvider getApiKeyProvider
	 */
	public function testGetApiKey ($provided) {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$this->assertEquals(self::$apikey, $AbstractDataHandler->getApiKey());
		$AbstractDataHandler->setApiKey($provided);
		$this->assertEquals($provided, $AbstractDataHandler->getApiKey());
	}
	
	public function getApiKeyProvider() {
		return array(
					array('4545434ff4493tej394gf343',),
				);
	}
	
	public function testGetRequest() {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$request = self::getProperty('_request');
	
		$testdata = 'sometestdata';
	
		$request->setValue($AbstractDataHandler, $testdata);
	
		$this->assertEquals($testdata, $AbstractDataHandler->getRequest());
	}
	
	
	public function testGetRawRequest() {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$raw_request = self::getProperty('_rawRequest');
		
		$testdata = 'sometestdata';
		
		$raw_request->setValue($AbstractDataHandler, $testdata);
		
		$this->assertEquals($testdata, $AbstractDataHandler->getRawRequest());
		
	}
	
	
	public function testGetRawResponse() {
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',array(self::$configkey));
		
		$raw_response = self::getProperty('_rawResponse');
		
		$testdata = 'sometestdata';
		
		$raw_response->setValue($AbstractDataHandler, $testdata);
			
		$this->assertEquals($testdata, $AbstractDataHandler->getRawResponse());
	}
}