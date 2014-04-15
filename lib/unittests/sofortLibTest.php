<?php

if(!class_exists('SofortLibTest'))
{
	abstract class SofortLibTest extends PHPUnit_Framework_TestCase {
		
		protected static $user_id = '12345';
		protected static $project_id = '67890';
		protected static $apikey = 'n3v4zt98nu580v4590jm395vut34ßnv43354';
		protected static $configkey = '12345:67890:n3v4zt98nu580v4590jm395vut34ßnv43354';
		protected static $testapi_url = 'http://www.google.de/test/';
		//protected static $testapi_url = 'https://api.sofort.com/api/xml';
		
		protected static $ideal_userid  = '12345';
		protected static $ideal_projectid  = '67890';
		protected static $ideal_configkey  = '12345:67890:n3v4zt98nu580v4590jm395vut34ßnv43354'; //your configkey or userid:projektid:apikey
		protected static $ideal_password = 'password';
		protected static $ideal_secret =  'secret_phrase';
	}
}