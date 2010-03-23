<?php

App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.RequestProxyController');
App::import('Controller', 'OauthLib.OauthLibAppController');

class RequestProxyControllerTest extends CakeTestCase {

	public function testHeaderParsed() {
		$request = null;
		$requestProxyController = & new RequestProxyController($request);
		$_ENV['Authorization'] = "OAuth realm=\"\", oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"26g7wHTtNO6ZWJaLltcueppHYiI%3D\", oauth_version=\"1.0\"";
		$required = array('oauth_consumer_key' => 'consumer_key_86cad9',
			'oauth_nonce' => '225579211881198842005988698334675835446',
			'oauth_signature' => '26g7wHTtNO6ZWJaLltcueppHYiI=',
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => '1199645624',
			'oauth_token' => 'token_411a7f',
			'oauth_version' => '1.0');
		$this->assertEqual($required, $requestProxyController->headerParams());
	}

	public function testParametersParsed() {
		App::import('Controller', 'AppController');
		$request = & new OauthLibAppController();
		$request->params['url'] = array('test' => 'data');
		$requestProxyController = & new RequestProxyController($request);
		$_ENV['Authorization'] = "OAuth realm=\"\", oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"26g7wHTtNO6ZWJaLltcueppHYiI%3D\", oauth_version=\"1.0\"";
		$required = array('oauth_consumer_key' => 'consumer_key_86cad9',
			'oauth_nonce' => '225579211881198842005988698334675835446',
			'oauth_signature' => '26g7wHTtNO6ZWJaLltcueppHYiI=',
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => '1199645624',
			'oauth_token' => 'token_411a7f',
			'oauth_version' => '1.0',
			'test' => 'data');
		$this->assertEqual($required, $requestProxyController->parameters());
		$_SERVER['REQUEST_URI'] = '/test';
		$_SERVER['SERVER_NAME'] = 'www.org';
		$this->assertEqual($requestProxyController->uri(), 'http://www.org/test');
		$_SERVER['HTTPS'] = 'https';
		$_SERVER['SERVER_PORT'] = 3128;
		$this->assertEqual($requestProxyController->uri(), 'https://www.org/test');

		$_SERVER['HTTPS'] = null;
		$_SERVER['SERVER_PORT'] = 8080;
		$this->assertEqual($requestProxyController->uri(), 'http://www.org:8080/test');
		
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$this->assertEqual($requestProxyController->method(), 'GET');
	}
	
	protected function _getRequestProxy($parameters, $options = array()) {
		$request = & new OauthLibAppController();
		$request->data = $parameters;
		$request->params['url'] = array('url' => '/', 'ext' => 'html');
	    $_ENV['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		$_ENV['Authorization'] = null;
		return RequestFactory::proxy($request, $options);
	}
 
  
 
	public function testParameterKeysShouldPreserveBracketsFromHash() {
		$proxy = $this->_getRequestProxy(array('message' => array('body' => 'This is a test')));
		$this->assertEqual(array('message' => array('body' => 'This is a test')), $proxy->parametersForSignature());
	}
	
	public function testParameterKeysShouldPreserveParameters() {
		$proxy = $this->_getRequestProxy(array('message' => array('body' => 'This is a test')));
		$this->assertEqual(array('message' => array('body' => 'This is a test')), $proxy->parametersForSignature());
	}

	public function testParameterClobberRequestCheck() {
		$proxy = $this->_getRequestProxy(array('message' => array('body' => 'This is a test')), array('clobber_request' => true, 'parameters' => array('a' => 'b')));
		$this->assertEqual(array('a' => 'b'), $proxy->parameters());
		$proxy = $this->_getRequestProxy(array('message' => array('body' => 'This is a test')), array('clobber_request' => true));
		$this->assertEqual(array(), $proxy->parameters());
	}	

}
?>