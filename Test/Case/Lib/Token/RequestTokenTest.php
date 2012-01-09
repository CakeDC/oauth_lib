<?php

App::import('Lib', 'OauthLib.RequestToken');
App::import('Lib', 'OauthLib.Consumer');

class StubbedToken extends RequestToken {
	public function buildAuthorizeUrlPromoted($rootDomain, $params) {
		return $this->_buildAuthorizeUrl($rootDomain, $params);
	}
}

class RequestTokenTest extends CakeTestCase {

	public function setup() {
		$this->RequestToken = new RequestToken(new Consumer('key', 'secret', array()), 'key', 'secret');
	}

	public function testRequestTokenBuildsAuthorizeUrlConnectlyWithAdditionalParams() {
		$authUrl = $this->RequestToken->authorizeUrl(array('oauth_callback' => 'github.com'));
		$this->assertNotNull($authUrl);
		$this->assertPattern('/oauth_token/', $authUrl);
		$this->assertPattern('/oauth_callback/', $authUrl);
	}

	public function testRequestTokenBuildsAuthorizeUrlConnectlyWithNoOrNullParams() {
		$authUrl = $this->RequestToken->authorizeUrl(null);
		$this->assertNotNull($authUrl);
		$this->assertPattern('/\?oauth_token=/', $authUrl);

		$authUrl = $this->RequestToken->authorizeUrl();
		$this->assertNotNull($authUrl);
		$this->assertPattern('/\?oauth_token=/', $authUrl);
	}

	public function testBuildAuthorizeUrl() {
		$StubbedToken = new StubbedToken(null, null, null);
		$url = $StubbedToken->buildAuthorizeUrlPromoted('http://github.com/oauth/authorize',array('foo' => 'bar bar'));
		$this->assertNotNull($url);
		$this->assertEqual("http://github.com/oauth/authorize?foo=bar+bar", $url);
	}

}
?>
