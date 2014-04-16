<?php
require_once(dirname(__FILE__).'/../core/sofortLibHttp.inc.php');
require_once('sofortLibTest.php');

class Unit_SofortLibHttpTest extends SofortLibTest {
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('SofortLibHttp');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	/**
	 * @dataProvider constructProvider
	 */
	public function testConstructor($provided) {
		
		if(count($provided) == 3) {
			$SofortLibHttp = new SofortLibHttp ($provided[0], $provided[1], $provided[2]);
			$this->assertEquals($provided[1], $SofortLibHttp->compression);
			$this->assertEquals($provided[2], $SofortLibHttp->proxy);
		} else if (count($provided) == 2) {
			$SofortLibHttp = new SofortLibHttp ($provided[0], $provided[1]);
			$this->assertEquals($provided[1], $SofortLibHttp->compression);
		} else {
			$SofortLibHttp = new SofortLibHttp ($provided[0]);
		}
		
		$this->assertEquals($provided[0], $SofortLibHttp->url);
	}
	
	
	public function constructProvider () {
		return array(
					array(array('http://www.test.de',),),
					array(array('http://www.test.de', true),),
					array(array('http://www.test.de', false),),
					array(array('http://www.test.de', true, 'someProxy'),),
					array(array('http://www.test.de', false, 'someProxy'),),
				);
	}
	
	
	public function testSetConfigKey () {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->setConfigKey(self::$configkey);
		
		$this->assertAttributeEquals(
				self::$configkey,  /* expected value */
				'_configKey',  /* attribute name */
				$SofortLibHttp /* object         */
		);
		
	}
	
	
	public function testSetHeaders () {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->setConfigKey(self::$configkey);
		$SofortLibHttp->setHeaders();
		
		$expected = array(
							'Authorization: Basic ' . base64_encode(self::$user_id.':'.self::$apikey),
							'Content-Type: application/xml; charset=UTF-8',
							'Accept: application/xml; charset=UTF-8',
							'X-Powered-By: PHP.*',
				);
// 		$expected = serialize(array(
// 							'Authorization: Basic MTAxMDo3ZGE4NGYwNTA3NTdhODU5ZGYwNzZkZDQyMDcyNTRiZQ==',
// 							'Content-Type: application/xml; charset=UTF-8',
// 							'Accept: application/xml; charset=UTF-8',
// 							'X-Powered-By: PHP/5.3.3-7+squeeze14',
// 				));
		
		$headers = $SofortLibHttp->headers;
		
		foreach ($expected as $i => $reg) {
			$this->assertRegExp('#'.$reg.'#', $headers[$i]);
		}
	}
	
	
	/**
	 * @dataProvider getInfoProvider
	 */
	public function testGetInfo ($provided, $expected, $opt = '') {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->info = $provided;
		
		if($opt == '') {
			$this->assertEquals($expected, $SofortLibHttp->getInfo());
		} else {
			$this->assertEquals($expected, $SofortLibHttp->getInfo($opt));
		}
	}
	
	
	public function getInfoProvider () {
		return array(
					array(array('test'), array('test'),),
					array(array('test', 'wusel',), array('test', 'wusel',),),
					array(array('http_code' => 'test', 'status' => 'wusel',), 'test', 'http_code'),
					array(array('http_code' => 'test', 'status' => 'wusel',), 'wusel', 'status'),
				);
	}
	
	
	/**
	 * @dataProvider getHttpCodeProvider
	 */
	public function testGetHttpCode ($provided, $expected) {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->httpStatus = $provided[0];
		$SofortLibHttp->url = $provided[1];
		
		$response = self::getProperty('_response');
		
		$response->setValue($SofortLibHttp, $provided[2]);
		
		$this->assertEquals($expected, $SofortLibHttp->getHttpCode());
	}
	
	
	public function getHttpCodeProvider () {
		return array(
					array(array(4711, 'http://www.google.de', 'test',), array('code' => 4711, 'message' => '<errors><error><code>04711</code><message>Something went wrong, not handled httpStatus</message></error></errors>', 'response' => 'test'),),
					array(array(200, 'http://www.google.de', 'test',), array('code' => 200, 'message' => '<errors><error><code>0200</code><message>OK</message></error></errors>', 'response' => 'test'),),
					array(array(301, 'http://www.google.de', 'test',), array('code' => 301, 'message' => '<errors><error><code>0301</code><message>Redirected Request</message></error></errors>', 'response' => 'test'),),
					array(array(302, 'http://www.google.de', 'test',), array('code' => 302, 'message' => '<errors><error><code>0302</code><message>Redirected Request</message></error></errors>', 'response' => 'test'),),
					array(array(401, 'http://www.google.de', 'test',), array('code' => 401, 'message' => '<errors><error><code>0401</code><message>Unauthorized</message></error></errors>', 'response' => 'test'),),
					array(array(0, 'http://www.google.de', '',), array('code' => 404, 'message' => '<errors><error><code>0404</code><message>URL not found http://www.google.de</message></error></errors>', 'response' => ''),),
					array(array(404, 'http://www.google.de', '',), array('code' => 404, 'message' => '<errors><error><code>0404</code><message>URL not found http://www.google.de</message></error></errors>', 'response' => ''),),
					array(array(500, 'http://www.google.de', 'test',), array('code' => 500, 'message' => '<errors><error><code>0500</code><message>An error occurred</message></error></errors>', 'response' => 'test'),),
				);
	}
	
	
	/**
	 * @dataProvider getHttpStatusCodeProvider
	 */
	public function testGetHttpStatusCode ($provided, $expected) {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->httpStatus = $provided[0];
		$SofortLibHttp->url = $provided[1];
		
		$response = self::getProperty('_response');
		
		$response->setValue($SofortLibHttp, $provided[2]);
		
		$this->assertEquals($expected, $SofortLibHttp->getHttpStatusCode());
	}
	
	
	public function getHttpStatusCodeProvider () {
		return array(
					array(array(4711, 'http://www.google.de', 'test',), 4711,),
					array(array(200, 'http://www.google.de', 'test',), 200,),
					array(array(301, 'http://www.google.de', 'test',), 301,),
					array(array(302, 'http://www.google.de', 'test',), 302,),
					array(array(401, 'http://www.google.de', 'test',), 401,),
					array(array(0, 'http://www.google.de', '',), 404,),
					array(array(404, 'http://www.google.de', '',), 404,),
					array(array(500, 'http://www.google.de', 'test',), 500,),
				);
	}
	
	
	/**
	 * @dataProvider getHttpStatusMessageProvider
	 */
	public function testGetHttpStatusMessage ($provided, $expected) {
		$SofortLibHttp = new SofortLibHttp (self::$testapi_url);
		
		$SofortLibHttp->httpStatus = $provided[0];
		$SofortLibHttp->url = $provided[1];
		
		$response = self::getProperty('_response');
		
		$response->setValue($SofortLibHttp, $provided[2]);
		
		$this->assertEquals($expected, $SofortLibHttp->getHttpStatusMessage());
	}
	
	
	public function getHttpStatusMessageProvider () {
		return array(
					array(array(4711, 'http://www.google.de', 'test',), '<errors><error><code>04711</code><message>Something went wrong, not handled httpStatus</message></error></errors>',),
					array(array(200, 'http://www.google.de', 'test',), '<errors><error><code>0200</code><message>OK</message></error></errors>',),
					array(array(301, 'http://www.google.de', 'test',), '<errors><error><code>0301</code><message>Redirected Request</message></error></errors>',),
					array(array(302, 'http://www.google.de', 'test',), '<errors><error><code>0302</code><message>Redirected Request</message></error></errors>',),
					array(array(401, 'http://www.google.de', 'test',), '<errors><error><code>0401</code><message>Unauthorized</message></error></errors>',),
					array(array(0, 'http://www.google.de', '',), '<errors><error><code>0404</code><message>URL not found http://www.google.de</message></error></errors>',),
					array(array(404, 'http://www.google.de', '',), '<errors><error><code>0404</code><message>URL not found http://www.google.de</message></error></errors>',),
					array(array(500, 'http://www.google.de', 'test',), '<errors><error><code>0500</code><message>An error occurred</message></error></errors>',),
				);
	}
}