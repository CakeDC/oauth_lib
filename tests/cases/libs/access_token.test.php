<?php

App::import('Lib', 'OauthLib.AccessToken');
App::import('Lib', 'OauthLib.Consumer');

class AccessTokenTest extends CakeTestCase {

	public function setup() {
		$this->fakeResponse = array('user_id' => 5734758743895, 'oauth_token' => 'key', 'oauth_token_secret' => 'secret');
		$this->AccessToken = new AccessToken(new Consumer('key', 'secret', array()), 'key', 'secret', $this->fakeResponse);
	}

	public function testAccessTokenMakesNonOauthResponseParamsAvailable() {
		$this->assertNotNull($this->AccessToken);
		$this->assertNotNull($this->AccessToken->params['user_id']);
		$this->assertEqual(5734758743895, $this->AccessToken->params['user_id']);
	}

}
?>