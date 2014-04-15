<?php
require_once(dirname(__FILE__).'/../core/sofortLibNotification.inc.php');
require_once('sofortLibTest.php');

class Unit_SofortLibNotificationTest extends SofortLibTest {
	
	protected static function getProperty($name) {
		$class = new ReflectionClass('SofortLibNotification');
		$property = $class->getProperty($name);
		$property->setAccessible(true);
		return $property;
	}
	
	
	public function testGetNotification() {
		$SofortLibNotification = new SofortLibNotification();
		
		$statusNotification = '';
		
		$this->assertFalse($SofortLibNotification->getNotification($statusNotification));
		
		$statusNotification = '<?xml version="1.0" encoding="UTF-8"?>
<status_notification>
<transaction>1324-1234-5483-4891</transaction>
<time>2010-04-14T19:01:08+02:00</time>
</status_notification>';
		
		$notification = '1324-1234-5483-4891';
		
		$this->assertEquals($notification, $SofortLibNotification->getNotification($statusNotification));
		
		$statusNotification = '<?xml version="1.0" encoding="UTF-8"?>
<status_test>
<transaction>1324-1234-5483-4891</transaction>
<time>2010-04-14T19:01:08+02:00</time>
</status_test>';
		
		$this->assertFalse($SofortLibNotification->getNotification($statusNotification));
		
		$statusNotification = '<?xml version="1.0" encoding="UTF-8"?>
<status_notification>
<nontransaction>1324-1234-5483-4891</nontransaction>
<time>2010-04-14T19:01:08+02:00</time>
</status_notification>';
		
		$this->assertFalse($SofortLibNotification->getNotification($statusNotification));
	}
	
	
	public function testGetTime() {
		$time = self::getProperty('_time');
		$SofortLibNotification = new SofortLibNotification(self::$configkey);
		
		$testTime = '2010-04-14T19:01:08+02:00';
		
		$time->setValue($SofortLibNotification, $testTime);
		
		$this->assertEquals($testTime, $SofortLibNotification->getTime());
	}
	
	
	public function testGetTransactionId() {
		$transactionId = self::getProperty('_transactionId');
		$SofortLibNotification = new SofortLibNotification(self::$configkey);
		
		$testTransactionId = '1324-1234-5483-4891';
		
		$transactionId->setValue($SofortLibNotification, $testTransactionId);
		
		$this->assertEquals($testTransactionId, $SofortLibNotification->getTransactionId());
	}
}