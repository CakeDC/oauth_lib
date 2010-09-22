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
App::import('Vendor', 'OauthLib.HttpSocket');
App::import('Lib', 'OauthLib.RequestFactory');
App::import('Lib', 'OauthLib.RequestProxyController');
App::import('Lib', 'OauthLib.Consumer');
App::import('Lib', 'OauthLib.ConsumerToken');
App::import('Controller', 'OauthLib.OauthAppController');
require_once APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'library' . DS . 'uri.php';

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class ClientHttpTest extends OauthTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->consumer = new Consumer('consumer_key_86cad9', '5888bf0345e5d237');
		$this->ConsumerToken = new ConsumerToken($this->consumer, 'token_411a7f', '3196ffd991c8ebdb');
		$this->requestUri = & new URI('http://example.com/test?key=value');
		$this->requestParameters = array('key' => 'value');
		$this->nonce = "225579211881198842005988698334675835446";
		$this->timestamp = "1199645624";
		$config = array('host' => 'example.com', 'request' => array('uri' => array('host' => 'example.com')));
		$this->http = new HttpSocket($config);
		$this->requestUriN = $this->http->parseUri('http://example.com/test?key=value');
	}

/**
 * testThatUsingAuthHeadersOnGetRequestsWorks
 *
 * @return void
 */
	public function testThatUsingAuthHeadersOnGetRequestsWorks() {
		$request = & new ClientHttp($this->http, $this->requestUriN['path'] . "?" . $this-> requestParametersToS());
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('GET', $request->method);
		$this->assertEqual('/test?key=value', $request->localPath());
		$this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"1oO2izFav1GP4kEH2EskwXkCRFg%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatUsingAuthHeadersOnPostRequestsWorks
 *
 * @return void
 */
	public function testThatUsingAuthHeadersOnPostRequestsWorks() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array(), 'POST');
		$request->setFormData($this->requestParameters);
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('POST', $request->method);
		$this->assertEqual('/test', $request->localPath());
		$this->assertEqual('key=value', $request->body());
		$this->assertEqual($this->toOrderedArray("OAuth oauth_nonce=\"225579211881198842005988698334675835446\", oauth_signature_method=\"HMAC-SHA1\", oauth_token=\"token_411a7f\", oauth_timestamp=\"1199645624\", oauth_consumer_key=\"consumer_key_86cad9\", oauth_signature=\"26g7wHTtNO6ZWJaLltcueppHYiI%3D\", oauth_version=\"1.0\""), $this->toOrderedArray($request->authorization));
	}

/**
 * testThatUsingPostParamsWorks
 *
 * @return void
 */
	public function testThatUsingPostParamsWorks() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array(), 'POST');
		$request->setFormData($this->requestParameters);
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('scheme' => 'body', 'nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('POST', $request->method);
		$this->assertEqual('/test', $request->localPath());
		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=26g7wHTtNO6ZWJaLltcueppHYiI%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($request->body()));
		$this->assertEqual(null, $request->authorization);
	}

/**
 * testThatUsingGetParamsWorks
 *
 * @return void
 */
	public function testThatUsingGetParamsWorks() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'] . "?" . $this->requestParametersToS());
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('scheme' => 'query_string', 'nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('GET', $request->method);

		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=1oO2izFav1GP4kEH2EskwXkCRFg%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($request->query()));
		$this->assertNull($request->authorization);
	}

/**
 * testThatUsingGetParamsWorksWithPostRequests
 *
 * @return void
 */
	public function testThatUsingGetParamsWorksWithPostRequests() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'] . "?" . $this->requestParametersToS(), array(), 'POST');
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('scheme' => 'query_string', 'nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('POST', $request->method);
		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=26g7wHTtNO6ZWJaLltcueppHYiI%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($request->query()));
		$this->assertEqual('', $request->body());
		$this->assertNull($request->authorization);
	}

/**
 * testThatUsingGetParamsWorksWithPostRequestsThatHavePostBodies
 *
 * @return void
 */
	public function testThatUsingGetParamsWorksWithPostRequestsThatHavePostBodies() {
		$request = &new ClientHttp($this->http, $this->requestUri->path . "?" . $this->requestParametersToS(), array(), 'POST');
		$request->setFormData(array('key2' => 'value2'));
		$request->oauth($this->http, $this->consumer, $this->ConsumerToken, array('scheme' => 'query_string', 'nonce' => $this->nonce, 'timestamp' => $this->timestamp));
		$this->assertEqual('POST', $request->method);
		$this->assertEqual($this->toOrderedArray("key=value&oauth_consumer_key=consumer_key_86cad9&oauth_nonce=225579211881198842005988698334675835446&oauth_signature=4kSU8Zd1blWo3W6qJH7eaRTMkg0%3D&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1199645624&oauth_token=token_411a7f&oauth_version=1.0"), $this->toOrderedArray($request->query()));
		$this->assertEqual("key2=value2", $request->body());
		$this->assertEqual(null, $request->authorization);
	}

/**
 * testExampleFromSpecs
 *
 * @return void
 */
	public function testExampleFromSpecs() {
		$this->consumer->initConsumer("dpf43f3p2l4k3l03", "kd94hf93k423kf44");
		$this->ConsumerToken = new ConsumerToken($this->consumer, 'nnch734d00sl2jdk', 'pfkkdhi9sl3r4s00');
		$requestUri = &new URI('http://photos.example.net/photos?file=vacation.jpg&size=original');
		$nonce = 'kllo9940pd9333jh';
		$timestamp = "1191242096";
		$config = array('host' => 'photos.example.net', 'request' => array('uri' => array('host' => 'photos.example.net')));
		$http = new HttpSocket($config);
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->query);
		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $this->ConsumerToken, array('nonce' => $nonce, 'timestamp' => $timestamp));
		$this->assertEqual($this->toOrderedArray('GET&http%3A%2F%2Fphotos.example.net%2Fphotos&file%3Dvacation.jpg%26oauth_consumer_key%3Ddpf43f3p2l4k3l03%26oauth_nonce%3Dkllo9940pd9333jh%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1191242096%26oauth_token%3Dnnch734d00sl2jdk%26oauth_version%3D1.0%26size%3Doriginal'), $this->toOrderedArray($signatureBaseString));

		$request->oauth($http, $this->consumer, $this->ConsumerToken, array('nonce' => $nonce, 'timestamp' => $timestamp, 'realm' => "http://photos.example.net/"));
		$this->assertEqual('GET', $request->method);
		$this->assertEqual($this->toOrderedArray('OAuth realm="http://photos.example.net/", oauth_nonce="kllo9940pd9333jh", oauth_signature_method="HMAC-SHA1", oauth_token="nnch734d00sl2jdk", oauth_timestamp="1191242096", oauth_consumer_key="dpf43f3p2l4k3l03", oauth_signature="tR3%2BTy81lMeYAr%2FFid0kMTYa%2FWM%3D", oauth_version="1.0"'), $this->toOrderedArray($request->authorization));
	}

/**
 * testStepByStepTokenRequest
 *
 * @return void
 * @author Predominant
 */
	public function testStepByStepTokenRequest() {
		$this->consumer->initConsumer("key","secret");
		$requestUri = & new URI('http://term.ie/oauth/example/request_token.php');
		$nonce = rand(0, pow(2,128));
		$timestamp = time();
		$config = array('host' => 'term.ie', 'request' => array('uri' => array('host' => 'term.ie')));
		$http = new HttpSocket($config);
		$token = null;

		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp));
		$this->assertEqual($this->toOrderedArray("GET&http%3A%2F%2Fterm.ie%2Foauth%2Fexample%2Frequest_token.php&oauth_consumer_key%3Dkey%26oauth_nonce%3D{$nonce}%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D{$timestamp}%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));

		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());
		$request->oauth($http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp));
		$this->assertEqual('GET', $request->method);
		$this->assertEqual('', $request->body());
		$this->assertEqual('', $request->authorization);
		$response = $request->request();
		$this->assertEqual("200", $response['status']['code']);
		$this->assertEqual("oauth_token=requestkey&oauth_token_secret=requestsecret", $response['body']);
	}

/**
 * testThatPutBodiesNotSigned
 *
 * @return void
 */
	function testThatPutBodiesNotSigned() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array("Content-Type" => "application/xml"), 'PUT');
		$request->body("<?xml version=\"1.0\"<foo><bar>baz</bar></foo>");
		$signatureBaseString = $request->signatureBaseString($this->http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp));
		$this->assertEqual($this->toOrderedArray("PUT&http%3A%2F%2Fexample.com%2Ftest&oauth_consumer_key%3Dconsumer_key_86cad9%26oauth_nonce%3D225579211881198842005988698334675835446%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1199645624%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));
	}

/**
 * testThatPutBodiesNotSignedIfFormUrlencoded
 *
 * @return void
 */
	function testThatPutBodiesNotSignedIfFormUrlencoded() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array(), 'PUT');
		$request->setFormData(array('key2' => 'value2'));
		$token = null;
		$signatureBaseString = $request->signatureBaseString($this->http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp));
		$this->assertEqual($this->toOrderedArray("PUT&http%3A%2F%2Fexample.com%2Ftest&oauth_consumer_key%3Dconsumer_key_86cad9%26oauth_nonce%3D225579211881198842005988698334675835446%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1199645624%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));
	}

/**
 * testThatPostBodiesSignedIfFormUrlencoded
 *
 * @return void
 */
	function testThatPostBodiesSignedIfFormUrlencoded() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array(), 'POST');
		$request->setFormData(array('key2' => 'value2'));
		$token = null;
		$signatureBaseString = $request->signatureBaseString($this->http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp));
		$this->assertEqual($this->toOrderedArray("POST&http%3A%2F%2Fexample.com%2Ftest&key2%3Dvalue2%26oauth_consumer_key%3Dconsumer_key_86cad9%26oauth_nonce%3D225579211881198842005988698334675835446%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1199645624%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));
	}

/**
 * testThatPostBodiesNotSignedIfOtherContentType
 *
 * @return void
 */
	function testThatPostBodiesNotSignedIfOtherContentType() {
		$request = &new ClientHttp($this->http, $this->requestUriN['path'], array("Content-Type" => "application/xml"), 'POST');
		$request->body("<?xml version=\"1.0\"<foo><bar>baz</bar></foo>");
		$token = null;
		$signatureBaseString = $request->signatureBaseString($this->http, $this->consumer, $token, array('scheme'  =>  'query_string', 'nonce'  =>  $this->nonce, 'timestamp'  =>  $this->timestamp));
		$this->assertEqual($this->toOrderedArray("POST&http%3A%2F%2Fexample.com%2Ftest&oauth_consumer_key%3Dconsumer_key_86cad9%26oauth_nonce%3D225579211881198842005988698334675835446%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1199645624%26oauth_version%3D1.0"), $this->toOrderedArray($signatureBaseString));
	}
}
