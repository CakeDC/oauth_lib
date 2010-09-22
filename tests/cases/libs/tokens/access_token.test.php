<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::import('Lib', 'OauthLib.AccessToken');
App::import('Lib', 'OauthLib.Consumer');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs.tokens
 */
class AccessTokenTest extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->fakeResponse = array('user_id' => 5734758743895, 'oauth_token' => 'key', 'oauth_token_secret' => 'secret');
		$this->AccessToken = new AccessToken(new Consumer('key', 'secret', array()), 'key', 'secret', $this->fakeResponse);
	}

/**
 * testAccessTokenMakesNonOauthResponseParamsAvailable
 *
 * @return void
 */
	public function testAccessTokenMakesNonOauthResponseParamsAvailable() {
		$this->assertNotNull($this->AccessToken);
		$this->assertNotNull($this->AccessToken->params['user_id']);
		$this->assertEqual(5734758743895, $this->AccessToken->params['user_id']);
	}
}
