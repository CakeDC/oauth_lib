<?php
/**
 * Oauth authentication tests
 *
 * Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('RequestFactory', 'OauthLib.Lib');
App::uses('ConsumerToken', 'OauthLib.Token');
App::uses('Consumer', 'OauthLib.Lib');
App::uses('HttpSocket', 'Network/Http');
require_once(CakePlugin::path('OauthLib') . 'Test' . DS . 'OauthTestCase.php');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class OauthAuthenticationTest extends OauthTestCase {

/**
 * testThatPlaintextSignatureWorks
 *
 * @return void
 */
	function testThatPlaintextSignatureWorks() {
		$auth = array(
			'Consumer' => array(
				'consumer_token' => 'key', 
				'consumer_secret' => 'secret'), 
			'Token' => array(
				'token' => 'accesskey',
				'secret' => 'accesssecret')
		);

		$request = array(
			'uri' => 'http://term.ie/oauth/example/echo_api.php?echo=hello',
			'method' => 'GET');
		$socket = new HttpSocket();
		$socket->configAuth('OauthLib.Oauth', $auth);
		$response = $socket->request($request);

		$this->assertNotNull($response);
		$this->assertEqual('200', $response->code);
		$this->assertEqual('echo=hello', $response->body);
		
	} 	
}
