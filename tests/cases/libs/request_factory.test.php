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

App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.ClientHttp');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class RequestFactoryTest extends CakeTestCase {

/**
 * testThatProxySimpleGetRequestWorks
 *
 * @return void
 */
	public function testThatProxySimpleGetRequestWorks() {
		$http = null;
		$request = &new ClientHttp($http, '/test?key=value');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test?key=value'));
		$expectedParameters = array('key' => array('value'));
		$this->assertEqual($expectedParameters, $requestProxy->parameters());
		$this->assertEqual('http://example.com/test', $requestProxy->uri());
		$this->assertEqual('GET', $requestProxy->method());
	}

/**
 * testThatProxySimplePostRequestWorks
 *
 * @return void
 */
	public function testThatProxySimplePostRequestWorks() {
		$http = null;
		$request = &new ClientHttp($http, '/test', array(), 'POST');
		$params = array('key' => 'value');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test', 'parameters' => $params));
		$expectedParameters = array('key' => array('value'));
		$this->assertEqual($expectedParameters, $requestProxy->parameters());
		$this->assertEqual('http://example.com/test', $requestProxy->uri());
		$this->assertEqual('POST', $requestProxy->method());
	}

/**
 * testThatProxyPostAndGetRequestWorks
 *
 * @return void
 */
	public function testThatProxyPostAndGetRequestWorks() {
		$http = null;
		$request = &new ClientHttp($http, '/test?key=value', array(), 'POST');
		$params = array('key2' => 'value2');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test?key=value', 'parameters' => $params));
		$expectedParameters = array('key' => array('value'), 'key2' => array('value2'));
		$this->assertEqual($expectedParameters, $requestProxy->parameters());
		$this->assertEqual('http://example.com/test', $requestProxy->uri());
		$this->assertEqual('POST', $requestProxy->method());
	}
}
