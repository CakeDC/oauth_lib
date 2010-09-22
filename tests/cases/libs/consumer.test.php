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
App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.ConsumerToken');
App::import('Lib', 'OauthLib.Consumer');
require_once APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'library' . DS . 'uri.php';

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class ConsumerTest extends OauthTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->consumer = new Consumer('consumer_key_86cad9', '5888bf0345e5d237',
			array('uri' => 'http://blabla.bla',
				'request_token_uri' => "/oauth/example/request_token.php",
				'access_token_uri' => "/oauth/example/access_token.php",
				'authorize_uri' => "/oauth/example/authorize.php",
				'scheme' => 'header',
				'http_method' => 'get'));

		$this->Token = new ConsumerToken($this->consumer, 'token_411a7f', '3196ffd991c8ebdb');
		$this->requestUri = new URI('http://example.com/test?key=value');
		$this->requestParameters = array('key' => 'value');
		$this->nonce = '225579211881198842005988698334675835446';
		$this->timestamp = '1199645624';
		$config = array('host' => 'example.com', 'request' => array('uri' => array('host' => 'example.com')));
		$this->consumer->http = new HttpSocket($config);
	}

/**
 * testInitializer
 *
 * @return void
 */
	public function testInitializer() {
	    $this->assertEqual('consumer_key_86cad9', $this->consumer->key);
	    $this->assertEqual('5888bf0345e5d237', $this->consumer->secret);
	    $this->assertEqual('http://blabla.bla', $this->consumer->site());
	    $this->assertEqual('/oauth/example/request_token.php', $this->consumer->requestTokenPath());
	    $this->assertEqual('/oauth/example/access_token.php', $this->consumer->accessTokenPath());
	    $this->assertEqual('http://blabla.bla/oauth/example/request_token.php', $this->consumer->requestTokenUrl());
	    $this->assertEqual('http://blabla.bla/oauth/example/access_token.php', $this->consumer->accessTokenUrl());
	    $this->assertEqual('http://blabla.bla/oauth/example/authorize.php', $this->consumer->authorizeUrl());
	    $this->assertEqual('header', $this->consumer->scheme());
	    $this->assertEqual('GET', $this->consumer->httpMethod());
	}

/**
 * testDefaults
 *
 * @return void
 */
	public function testDefaults() {
		$this->consumer = new Consumer('key', 'secret', array('uri' => 'http://test.com'));
	    $this->assertEqual('key', $this->consumer->key);
	    $this->assertEqual('secret', $this->consumer->secret);
	    $this->assertEqual('http://test.com', $this->consumer->site());
	    $this->assertEqual('/oauth/request_token', $this->consumer->requestTokenPath());
	    $this->assertEqual('/oauth/access_token', $this->consumer->accessTokenPath());
	    $this->assertEqual('http://test.com/oauth/request_token', $this->consumer->requestTokenUrl());
	    $this->assertEqual('http://test.com/oauth/access_token', $this->consumer->accessTokenUrl());
	    $this->assertEqual('http://test.com/oauth/authorize', $this->consumer->authorizeUrl());
	    $this->assertEqual('header', $this->consumer->scheme());
	    $this->assertEqual('POST', $this->consumer->httpMethod());
	}

/**
 * testOverridePaths
 *
 * @return void
 */
	public function testOverridePaths() {
		$this->consumer->initConsumer('key', 'secret', array(
			  'uri' => 'http://test.com',
			  'request_token_url' => 'http://oauth.test.com/request_token',
			  'access_token_url' => 'http://oauth.test.com/access_token',
			  'authorize_url' => 'http://site.test.com/authorize'));
		$this->assertEqual('key', $this->consumer->key);
		$this->assertEqual('secret', $this->consumer->secret);
		$this->assertEqual('http://test.com', $this->consumer->site());
		$this->assertEqual('/oauth/request_token', $this->consumer->requestTokenPath());
		$this->assertEqual('/oauth/access_token', $this->consumer->accessTokenPath());
		$this->assertEqual('http://oauth.test.com/request_token', $this->consumer->requestTokenUrl());
		$this->assertEqual('http://oauth.test.com/access_token', $this->consumer->accessTokenUrl());
		$this->assertEqual('http://site.test.com/authorize', $this->consumer->authorizeUrl());
		$this->assertEqual('header', $this->consumer->scheme());
		$this->assertEqual('POST', $this->consumer->httpMethod()); 
	}

/**
 * testThatSigningAuthHeadersOnGetRequestsWorks
 *
 * @return void
 */
	public function testThatSigningAuthHeadersOnGetRequestsWorks() {
		$request = new ClientHttp($this->consumer->http, $this->requestUri->path  . '?' . $this->requestParametersToS(), array(), 'GET');
		$this->Token->sign($request, array('nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('GET', $request->method);
		$this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"1oO2izFav1GP4kEH2EskwXkCRFg%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatSettingSignatureMethodOnConsumerEffectsSigning
 *
 * @return void
 */
 	public function testThatSettingSignatureMethodOnConsumerEffectsSigning() {
		$request = new ClientHttp($this->consumer->http, $this->requestUri->path  . '?' . $this->requestParametersToS(), array(), 'GET');
		$this->consumer->options['signature_method'] = 'PLAINTEXT';
		$this->Token->sign($request, array('nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		
		$this->assertNoPattern('/oauth_signature_method="HMAC-SHA1"/', $request->authorization);
		$this->assertPattern('/oauth_signature_method="PLAINTEXT"/', $request->authorization);
	} 

/**
 * testThatSettingSignatureMethodOnConsumerEffectsSignatureBaseString
 *
 * @return void
 */
	function testThatSettingSignatureMethodOnConsumerEffectsSignatureBaseString() {
		$request = new ClientHttp($this->consumer->http, $this->requestUri->path  . '?' . $this->requestParametersToS(), array(), 'GET');
		$this->consumer->options['signature_method'] = 'PLAINTEXT';

		$signatureBaseString = $this->consumer->signatureBaseString($request, null, array());
		$this->assertNoPattern('/HMAC-SHA1/', $signatureBaseString);
		$this->assertPattern("/{$this->consumer->secret}%26/", $signatureBaseString);
	}

/**
 * testThatPlaintextSignatureWorks
 *
 * @return void
 */
	function testThatPlaintextSignatureWorks() {
		$this->consumer->initConsumer('key', 'secret', array(
	        'uri' => 'http://term.ie',
	        'request_token_uri' => '/oauth/example/request_token.php',
	        'access_token_uri' => '/oauth/example/access_token.php',
	        'authorize_uri' => '/oauth/example/authorize.php',
	        'scheme' => 'header'));
		$options=array('nonce' => 'nonce', 'timestamp' => time());
		$this->requestUri = new URI('http://term.ie');
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$this->consumer->http = new HttpSocket($config);
		$request = new ClientHttp($this->consumer->http, "/oauth/example/request_token.php");
		$this->requestToken = $this->consumer->getRequestToken(array(), array(), $this->Token);
		$this->accessToken = $this->requestToken->getAccessToken();

		$response = $this->accessToken->get('/oauth/example/echo_api.php?echo=hello');
		$this->assertNotNull($response);
		$this->assertEqual('200', $response['status']['code']);
		$this->assertEqual('echo=hello', $response['body']);
		
	}

/**
 * testThatSigningAuthHeadersOnPostRequestsWorks
 *
 * @return void
 */
	public function testThatSigningAuthHeadersOnPostRequestsWorks() {
		$request = new ClientHttp($this->consumer->http, $this->requestUri->path, array(), 'POST');
	    $request->setFormData($this->requestParameters);
	    $this->Token->sign($request, array('nonce'  =>  $this->nonce, 'timestamp' => $this->timestamp));
	    $this->assertEqual('POST', $request->method);
	    $this->assertEqual('/test', $request->path);
	    $this->assertEqual('key=value', $request->body());
		$this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"26g7wHTtNO6ZWJaLltcueppHYiI%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatSigningPostParamsWorks
 *
 * @return void
 */
	public function testThatSigningPostParamsWorks() {
		$request = & new ClientHttp($this->consumer->http, $this->requestUri->path, array(), 'POST');
	    $request->setFormData($this->requestParameters);
	    $this->Token->sign($request, array('scheme'  =>  'body', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp));

	    $this->assertEqual('POST', $request->method);
	    $this->assertEqual('/test', $request->path);
		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=26g7wHTtNO6ZWJaLltcueppHYiI%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($this->sorting($request->body())));
	    $this->assertEqual(null, $request->authorization);
	}

/**
 * testThatUsingAuthHeadersOnGetOnCreateSignedRequestsWorks
 *
 * @return void
 */
	public function testThatUsingAuthHeadersOnGetOnCreateSignedRequestsWorks() {
		$request = $this->consumer->createSignedRequest($this->consumer->http, 'get', $this->requestUri->path . "?" . $this->requestParametersToS(), $this->Token, array('nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp),array('data'  =>  $this->requestParameters));
	    $this->assertEqual('GET', $request->method);
	    $this->assertEqual('/test?key=value', $request->path);
	    $this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"1oO2izFav1GP4kEH2EskwXkCRFg%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatUsingAuthHeadersOnPostOnCreateSignedRequestsWorks
 *
 * @return void
 */
	public function testThatUsingAuthHeadersOnPostOnCreateSignedRequestsWorks() {
		$request = $this->consumer->createSignedRequest($this->consumer->http, 'post', $this->requestUri->path,$this->Token, array('nonce' => $this->nonce, 'timestamp' => $this->timestamp), array('data'  =>  $this->requestParameters));
	    $this->assertEqual('POST', $request->method);
	    $this->assertEqual('/test', $request->path);
	    $this->assertEqual('key=value', $request->body());
	    $this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"26g7wHTtNO6ZWJaLltcueppHYiI%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatSigningPostParamsWorks2
 *
 * @return void
 */
	public function testThatSigningPostParamsWorks2() {
		$request = $this->consumer->createSignedRequest($this->consumer->http, 'post', $this->requestUri->path, $this->Token, array('scheme'  =>  'body', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp), array('data'  =>  $this->requestParameters));
	    $this->assertEqual('POST', $request->method);
	    $this->assertEqual('/test', $request->path);
		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=26g7wHTtNO6ZWJaLltcueppHYiI%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($this->sorting($request->body())));
	    $this->assertEqual(null, $request->authorization);
	}

/**
 * testStepByStepTokenRequest
 *
 * @return void
 */
	function testStepByStepTokenRequest() {
		$this->consumer->initConsumer('key', 'secret', array(
	        'uri' => 'http://term.ie',
	        'request_token_uri' => '/oauth/example/request_token.php',
	        'access_token_uri' => '/oauth/example/access_token.php',
	        'authorize_uri' => '/oauth/example/authorize.php',
	        'scheme' => 'header'));
		$options=array('nonce' => 'nonce', 'timestamp' => time());
		$this->requestUri = new URI('http://term.ie');
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$this->consumer->http = new HttpSocket($config);
		$request = new ClientHttp($this->consumer->http, "/oauth/example/request_token.php");

		$signatureBaseString = $this->consumer->signatureBaseString($request, null, $options);
		$this->assertEqual($this->toOrderedArray("GET&http%3A%2F%2Fterm.ie%2Foauth%2Fexample%2Frequest_token.php&oauth_consumer_key%3Dkey%26oauth_nonce%3D{$options['nonce']}%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D{$options['timestamp']}%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));
		$this->consumer->sign($request, null, $options);
	    $this->assertEqual('GET', $request->method);
	    $this->assertEqual(null, $request->body);
		$http = $this->consumer->http();
		$response = $request->request();
		$this->assertEqual('200', $response['status']['code']);
		$this->assertEqual('oauth_token=requestkey&oauth_token_secret=requestsecret',$response['body']);
	}

/**
 * testGetTokenSequence
 *
 * @return void
 */
	public function testGetTokenSequence() {
		$this->consumer->initConsumer('key', 'secret',
			array(
				'uri' => 'http://term.ie',
				'request_token_uri' => '/oauth/example/request_token.php',
				'access_token_uri' => '/oauth/example/access_token.php',
				'authorize_uri' => '/oauth/example/authorize.php'
				,'signature_method' => 'HMAC-SHA1'
				));

		$this->requestUri = new URI('http://term.ie');
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$this->consumer->http = new HttpSocket($config);
		$this->requestToken = $this->consumer->getRequestToken(array(), array(), $this->Token);
		$this->assertTrue($this->requestToken);
		$this->assertEqual('requestkey', $this->requestToken->token);
		$this->assertEqual('requestsecret', $this->requestToken->tokenSecret);
		$this->assertEqual('http://term.ie/oauth/example/authorize.php?oauth_token=requestkey', $this->requestToken->authorizeUrl());

		$this->accessToken = $this->requestToken->getAccessToken();
		$this->assertNotNull($this->accessToken);
		$this->assertEqual('accesskey', $this->accessToken->token);
		$this->assertEqual('accesssecret', $this->accessToken->tokenSecret);

		$response = $this->accessToken->get('/oauth/example/echo_api.php?ok=hello&test=this');
		$this->assertNotNull($response);
		$this->assertEqual('200', $response['status']['code']);
		$this->assertEqual('ok=hello&test=this', $response['body']);

		$response = $this->accessToken->post('/oauth/example/echo_api.php',array('ok' => 'hello','test' => 'this'));
		$this->assertNotNull($response);
		$this->assertEqual('200', $response['status']['code']);
		//debug($response);
		$this->assertEqual('ok=hello&test=this', $response['body']);
	}  

/**
 * testGetTokenSequence
 *
 * @return void
 */
	public function _testGetTokenWithAdditionalArguments() {
		$this->consumer->initConsumer('key', 'secret',
			array(
				'uri' => 'http://term.ie',
				'request_token_uri' => '/oauth/example/request_token.php',
				'access_token_uri' => '/oauth/example/access_token.php',
				'authorize_uri' => '/oauth/example/authorize.php'));
		$this->requestUri = new URI('http://term.ie');
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$this->consumer->http = new HttpSocket($config);

		$debug = '';
		$http = $this->consumer->http();
		$this->requestToken = $this->consumer->getRequestToken(array(), array('scope' => "http://www.google.com/calendar/feeds http://picasaweb.google.com/data"), $this->Token);
    
		$this->assertPattern('/"scope=http%3a%2f%2fwww.google.com%2fcalendar%2ffeeds%20http%3a%2f%2fpicasaweb.google.com%2fdata"/', $http->response['raw']['body']);
	}
}
