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

App::import('Lib', 'OauthLib.ClientHttp');
App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.Consumer');
App::import('Lib', 'OauthLib.ConsumerToken');
App::import('Lib', 'OauthLib.RequestProxyController');
App::import('Controller', 'OauthLib.OauthAppController');
require_once APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'library' . DS . 'uri.php';

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class ClientHttpTermieTest extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->consumer = new Consumer('consumer_key_86cad9', '5888bf0345e5d237');
		$this->requestUri = & new URI('http://example.com/test?key=value');
		$this->requestParameters = array('key' => 'value');
		$this->nonce = "225579211881198842005988698334675835446";
		$this->timestamp = "1199645624";
		$config = array('host' => 'example.com', 'request' => array('uri' => array('host' => 'example.com')));
		$this->http = new HttpSocket($config);
		$this->requestUriN = $this->http->parseUri('http://example.com/test?key=value');
	}

/**
 * testHmacSha1
 *
 * Need to analyze why term.ie some times return error
 *
 * @return void
 */
	public function testHmacSha1() {
		$signatureMethod = 'HMAC-SHA1';
		$this->consumer = new Consumer('key', 'secret');
		$requestUri = & new URI('http://term.ie/oauth/example/request_token.php');
		$nonce = rand(0, pow(2,128));
		$timestamp = time();
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$http = new HttpSocket($config);
		$token = null;
		
		$requestParams = array('scheme'  =>  'query_string', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp, 'signature_method'  => $signatureMethod);
		
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $token, $requestParams);
		$this->assertEqual("GET&http%3A%2F%2Fterm.ie%2Foauth%2Fexample%2Frequest_token.php&oauth_consumer_key%3Dkey%26oauth_nonce%3D{$nonce}%26oauth_signature_method%3D{$signatureMethod}%26oauth_timestamp%3D{$timestamp}%26oauth_version%3D1.0", $signatureBaseString);

		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$request->oauth($http, $this->consumer, $token, $requestParams);
		$this->assertEqual('GET', $request->method);
		$this->assertEqual('', $request->body());
		$this->assertEqual('', $request->authorization);
		
		$response = $request->request();
		$this->assertEqual("200", $response['status']['code']);
		$this->assertEqual("oauth_token=requestkey&oauth_token_secret=requestsecret", $response['body']);
	}
	
/**
 * testPlaintext
 * 
 * Need to analyze why term.ie some times return error
 *
 * @return void
 */
	public function testPlaintext() {
		$signatureMethod = 'PLAINTEXT';
		$this->consumer = new Consumer('key', 'secret');
		$requestUri = & new URI('http://term.ie/oauth/example/request_token.php');
		$nonce = rand(0, pow(2,128));
		$timestamp = time();
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$http = new HttpSocket($config);
		$token = null;
		
		$requestParams = array('scheme'  =>  'query_string', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp, 'signature_method'  => $signatureMethod);
		
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $token, $requestParams);
		$this->assertEqual("secret%26", $signatureBaseString);
		
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$request->oauth($http, $this->consumer, $token, $requestParams);
		$this->assertEqual('GET', $request->method);
		$this->assertEqual('', $request->body());
		$this->assertEqual('', $request->authorization);
		
		$response = $request->request();
		$this->assertEqual("200", $response['status']['code']);
		$this->assertEqual("oauth_token=requestkey&oauth_token_secret=requestsecret", $response['body']);
	}
	
/**
 * testRsaSha1
 *
 * Need to analyze why term.ie some times return error
 *
 * @return void
 */
	public function testRsaSha1() {
		$signatureMethod = 'RSA-SHA1';
		$this->consumer = new Consumer('key', 'secret');
		$requestUri = & new URI('http://term.ie/oauth/example/request_token.php');
		$nonce = rand(0, pow(2,128));
		$timestamp = time();
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$http = new HttpSocket($config);
		$token = null;
		
		$requestParams = array('scheme'  =>  'query_string', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp, 'signature_method'  => $signatureMethod);
		$privateFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.pem');
		$publicFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.cer');
		$requestParams['privateCert'] = $privateFile->read();
		$requestParams['publicCert'] = $publicFile->read();
		$requestParams['privateCertPass']      = '';
		
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $token, $requestParams);
		$this->assertEqual("GET&http%3A%2F%2Fterm.ie%2Foauth%2Fexample%2Frequest_token.php&oauth_consumer_key%3Dkey%26oauth_nonce%3D{$nonce}%26oauth_signature_method%3D{$signatureMethod}%26oauth_timestamp%3D{$timestamp}%26oauth_version%3D1.0", $signatureBaseString);

		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$request->oauth($http, $this->consumer, $token, $requestParams);
		$this->assertEqual('GET', $request->method);
		$this->assertEqual('', $request->body());
		$this->assertEqual('', $request->authorization);
		
		$response = $request->request();
		$this->assertEqual("200", $response['status']['code']);
		$this->assertEqual("oauth_token=requestkey&oauth_token_secret=requestsecret", $response['body']);
	}

/**
 * requestParametersToS
 *
 * @return string
 */
	protected function requestParametersToS() {
		$paramList = array();
		foreach($this->requestParameters as $k => $v) {
			$paramList[] = "$k=$v";
		}
		return implode("&", $paramList);
	}

/**
 * sorting
 *
 * @param string $data 
 * @return string
 */
	public function sorting($data) {
		$arr = explode('&', $data);
		sort($arr);
		return implode('&', $arr);
	}
}
