<?php

App::import('Lib', 'OauthLib.Token');

class TokenTest extends CakeTestCase {

	public function testTokenConstructorProducesValidToken() {
		$Token = new Token('xyz', '123');
		$this->assertEqual('xyz', $Token->token);
		$this->assertEqual('123', $Token->tokenSecret);
	}
}
?>