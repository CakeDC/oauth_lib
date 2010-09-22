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
App::import('Lib', 'OauthLib.RequestProxyController');
App::import('Lib', 'OauthLib.ClientHttp');

/**
 * RequestProxyHttpTest
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.cases.request_proxy
 */
class RequestProxyHttpTest extends CakeTestCase {

/**
 * __HttpObject
 *
 * @param string $uri 
 * @param string $method 
 * @param string $host 
 * @return void
 */
	private function __HttpObject($uri, $method, $host = 'example.com') {
		$config = array('host' => $host, 'request' => array('uri' => array('host' => $host)));
		$http = new HttpSocket($config);
		return new ClientHttp($http, $uri, array(), $method);	
	}

/**
 * testThatProxySimpleGetRequestWorks
 *
 * @return void
 */
	public function testThatProxySimpleGetRequestWorks() {
		$request = $this->__HttpObject('/test?key=value', 'GET');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test?key=value'));

		$expected = array('key' => array('value'));
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('GET', $requestProxy->method());
	}

/**
 * testThatProxySimplePostRequestWorksWithArguments
 *
 * @return void
 */
	public function testThatProxySimplePostRequestWorksWithArguments() {
		$request = $this->__HttpObject('/test', 'POST');
		$params = array('key' => 'value');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test', 'parameters' => $params));

		$expected = array('key' => array('value'));
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('POST', $requestProxy->method());
	}

/**
 * testThatProxySimplePostRequestWorksWithFormData
 *
 * @return void
 */
	public function testThatProxySimplePostRequestWorksWithFormData() {
		$request = $this->__HttpObject('/test', 'POST');
		$params = array('key' => 'value');
		$request->setFormData($params);
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test'));

		$expected = array('key' => array('value'));
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('POST', $requestProxy->method());
	}

/**
 * testThatProxySimplePutRequestWorksWithArgugments
 *
 * @return void
 */
	public function testThatProxySimplePutRequestWorksWithArgugments() {
		$request = $this->__HttpObject('/test', 'PUT');
		$params = array('key' => 'value');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test', 'parameters' => $params));

		$expected = array('key' => array('value'));
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('PUT', $requestProxy->method());
	}

/**
 * testThatProxySimplePutRequestWorksWithFormData
 *
 * @return void
 */
	public function testThatProxySimplePutRequestWorksWithFormData() {
		$request = $this->__HttpObject('/test', 'PUT');
		$params = array('key' => 'value');
		$request->setFormData($params);
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test'));

		$expected = array();
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('PUT', $requestProxy->method());
	}

/**
 * testThatProxyPostRequestWorksWithMixedParamSources
 *
 * @return void
 */
	public function testThatProxyPostRequestWorksWithMixedParamSources() {
		$request = $this->__HttpObject('/test?key=value', 'POST');
		$request->setFormData(array('key2' => array('value2')));
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test?key=value', 'parameters' => array('key3' => array('value3'))));

		$expected = array('key' => array('value'), 'key2' => array('value2'), 'key3' => array('value3'));
		$this->assertEqual($expected, $requestProxy->parametersForSignature());
		$this->assertEqual('http://example.com/test', $requestProxy->normalizedUri());
		$this->assertEqual('POST', $requestProxy->method());
	}
}
