<?php

App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.ClientHttp');

class RequestFactoryTest extends CakeTestCase {

	public function testThatProxySimpleGetRequestWorks() {
		$http = null;
		$request = &new ClientHttp($http, '/test?key=value');
		$requestProxy = RequestFactory::proxy($request, array('uri' => 'http://example.com/test?key=value'));
		$expectedParameters = array('key' => array('value'));
		$this->assertEqual($expectedParameters, $requestProxy->parameters());
		$this->assertEqual('http://example.com/test', $requestProxy->uri());
		$this->assertEqual('GET', $requestProxy->method());
	}

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
?>