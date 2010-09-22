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

App::import('Lib', 'OauthLib.RequestToken');
App::import('Lib', 'OauthLib.Consumer');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs.tokens
 */
class StubbedToken extends RequestToken {

/**
 * buildAuthorizeUrlPromoted
 *
 * @param string $rootDomain 
 * @param string $params 
 * @return string
 */
	public function buildAuthorizeUrlPromoted($rootDomain, $params) {
		return $this->_buildAuthorizeUrl($rootDomain, $params);
	}
}

/**
 * RequestTokenTest
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs.tokens
 */
class RequestTokenTest extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->RequestToken = new RequestToken(new Consumer('key', 'secret', array()), 'key', 'secret');
	}

/**
 * testRequestTokenBuildsAuthorizeUrlConnectlyWithAdditionalParams
 *
 * @return void
 */
	public function testRequestTokenBuildsAuthorizeUrlConnectlyWithAdditionalParams() {
		$authUrl = $this->RequestToken->authorizeUrl(array('oauth_callback' => 'github.com'));
		$this->assertNotNull($authUrl);
		$this->assertPattern('/oauth_token/', $authUrl);
		$this->assertPattern('/oauth_callback/', $authUrl);
	}

/**
 * testRequestTokenBuildsAuthorizeUrlConnectlyWithNoOrNullParams
 *
 * @return void
 */
	public function testRequestTokenBuildsAuthorizeUrlConnectlyWithNoOrNullParams() {
		$authUrl = $this->RequestToken->authorizeUrl(null);
		$this->assertNotNull($authUrl);
		$this->assertPattern('/\?oauth_token=/', $authUrl);

		$authUrl = $this->RequestToken->authorizeUrl();
		$this->assertNotNull($authUrl);
		$this->assertPattern('/\?oauth_token=/', $authUrl);
	}

/**
 * testBuildAuthorizeUrl
 *
 * @return void
 */
	public function testBuildAuthorizeUrl() {
		$StubbedToken = new StubbedToken(null, null, null);
		$url = $StubbedToken->buildAuthorizeUrlPromoted('http://github.com/oauth/authorize',array('foo' => 'bar bar'));
		$this->assertNotNull($url);
		$this->assertEqual("http://github.com/oauth/authorize?foo=bar+bar", $url);
	}
}
