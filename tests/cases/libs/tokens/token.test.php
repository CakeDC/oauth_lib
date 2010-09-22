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

App::import('Lib', 'OauthLib.Token');

/**
 * Oauth Tests
 *
 * @package 	oauth_lib
 * @subpackage 	oauth_lib.tests.libs.tokens
 */
class TokenTest extends CakeTestCase {

/**
 * testTokenConstructorProducesValidToken
 *
 * @return void
 */
	public function testTokenConstructorProducesValidToken() {
		$Token = new Token('xyz', '123');
		$this->assertEqual('xyz', $Token->token);
		$this->assertEqual('123', $Token->tokenSecret);
	}
}
