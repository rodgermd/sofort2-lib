<?php
require_once(dirname(__FILE__).'/../core/sofortLibMultipay.inc.php');
require_once('sofortLibTest.php');

/**
 * Class constructed just to test the methods of the abstract class
 * @author mm
 *
 */
class SofortLibMultipayMock extends SofortLibMultipay {

}

class Unit_SofortLibMultipayTest extends SofortLibTest {
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('SofortLibMultipayMock');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	/**
	 * @dataProvider getPaymentProvider
	 */
	public function testGetPaymentUrl ($provided) {
		$response = self::getProperty('_response');
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$test['new_transaction']['payment_url']['@data'] = $provided;
		
		$response->setValue($SofortLibMultipayMock, $test);
		
		$this->assertEquals($provided, $SofortLibMultipayMock->getPaymentUrl());
	}
	
	
	public function getPaymentProvider () {
		return array(
					array('http://www.google.de'),
					array('http://www.test.de'),
				);
	}
	
	
	/**
	 * @dataProvider getTransactionIdProvider
	 */
	public function testGetTransactionId ($provided) {
		$response = self::getProperty('_response');
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$test['new_transaction']['transaction']['@data'] = $provided;
		
		$response->setValue($SofortLibMultipayMock, $test);
		
		$this->assertEquals($provided, $SofortLibMultipayMock->getTransactionId());
	}
	
	
	public function getTransactionIdProvider () {
		return array(
					array('123324-3434354-4545454'),
					array('AS3324-45fFEr4-4545454'),
				);
	}
	
	
	/**
	 * @dataProvider senderAccountProvider
	 */
	public function testSetSenderAccount ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setSenderAccount($provided[0], $provided[1], $provided[2]);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, array($received['sender']['bank_code'], $received['sender']['account_number'], $received['sender']['holder']));
	}
	
	
	public function senderAccountProvider () {
		return array(
				array(array('88888888', '12345678', 'Max Mustermann')),
		);
	}
	
	
	/**
	 * @dataProvider languageCodeProvider
	 *
	 */
	public function testSetLanguageCode ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setLanguageCode($provided[0]);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided[1], $received['language_code']);
	}
	
	
	public function languageCodeProvider () {
		return array(
				array(array('DE', 'DE')),
				array(array('FR', 'FR')),
				array(array(NULL, 'EN')),
		);
	}
	
	
	/**
	 * @dataProvider timeoutProvider
	 *
	 */
	public function testSetTimeout ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setTimeout($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['timeout']);
	}
	
	
	public function timeoutProvider () {
		return array(
				array(100),
				array(50),
				array(NULL),
		);
	}
	
	
	/**
	 * @dataProvider amountProvider
	 */
	public function testSetAmount ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
	
		$SofortLibMultipayMock->setAmount($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['amount']);
	}
	
	
	public function amountProvider () {
		return array(
				array(20),
				array(10.13),
		);
	}
	
	
	
	
	/**
	 * @dataProvider emailCustomerProvider
	 *
	 */
	public function testSetEmailCustomer ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setEmailCustomer($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['email_customer']);
	}
	
	
	public function emailCustomerProvider () {
		return array(
				array('info@sofort.com'),
				array('test@test.de'),
				array('ererererre'),
		);
	}
	
	
	/**
	 * @dataProvider phoneCustomerProvider
	 *
	 */
	public function testSetPhoneCustomer ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setPhoneCustomer($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['phone_customer']);
	}
	
	
	public function phoneCustomerProvider () {
		return array(
				array('034545454'),
				array('045454545'),
				array('045454-454545'),
		);
	}
	
	
	/**
	 * @dataProvider userVariableProvider
	 */
	public function testSetUserVariable ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setUserVariable($provided);
		$received = $SofortLibMultipayMock->getParameters();
		if(!is_array($provided)) {
			$provided = array($provided);
		}
		$this->assertEquals($provided, $received['user_variables']['user_variable']);
	}
	
	
	public function userVariableProvider () {
		return array(
					array('http://www.google.de'),
					array(array('http://www.sofort.com', 'http://www.heise.de')),
				);
	}
	
	/**
	 * @dataProvider senderCountryCodeProvider
	 */
	public function testSetSenderCountryCode ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setSenderCountryCode($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['sender']['country_code']);
	}
	
	
	public function senderCountryCodeProvider () {
		return array(
				array('de'),
				array('fr'),
				array('br'),
		);
	}
	
	
	public function testGetReason () {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$this->assertFalse($SofortLibMultipayMock->getReason());
		
		$expected = array();
		$expected['reasons']['reason'] = 'test';
		
		$SofortLibMultipayMock->setParameters($expected);
		
		$this->assertEquals('test', $SofortLibMultipayMock->getReason());
	}
	
	/**
	 * @dataProvider reasonProvider
	 */
	public function testSetReason ($provided, $expected) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setReason($provided[0], $provided[1]);
		$this->assertEquals($expected, $SofortLibMultipayMock->getReason());
	}
	
	
	/**
	 * Dataprovider for testSetReason
	 * @return multitype:multitype:multitype:string   multitype:multitype:string NULL  multitype:string
	 */
	public function reasonProvider () {
		return array(
				array( array('Verwendungszweck', 'Zweite Zeile'), array('Verwendungszweck', 'Zweite Zeile')),
				array( array('Verwendungszweck', NULL), array('Verwendungszweck', '')),
				array( array('Verwendungszweck', '123456789012345678901234567890'), array('Verwendungszweck', '123456789012345678901234567')),
				array( array('Verwendungszweck', 'test@test'), array('Verwendungszweck', 'test test')),
		);
	}
	
	
	/**
	 * @dataProvider senderIbanProvider
	 */
	public function testSetSenderIban ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setSenderIban($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['sender']['iban']);
	}
	
	
	public function senderIbanProvider () {
		return array(
				array('DE8888888812345678'),
		);
	}
	
	
	/**
	 * @dataProvider senderBicProvider
	 */
	public function testSetSenderBic ($provided) {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$SofortLibMultipayMock->setSenderBic($provided);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($provided, $received['sender']['bic']);
	}
	
	
	public function senderBicProvider () {
		return array(
				array('MARKDEFF'),
		);
	}
	
	public function testSetVersion() {
		$SofortLibMultipayMock = new SofortLibMultipayMock(self::$configkey);
		
		$version = '12345';
		$SofortLibMultipayMock->setVersion($version);
		$received = $SofortLibMultipayMock->getParameters();
		$this->assertEquals($version, $received['interface_version']);
	}
}