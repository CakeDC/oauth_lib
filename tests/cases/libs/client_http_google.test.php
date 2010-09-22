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
App::import('Lib', 'OauthLib.RequestProxyController');
App::import('Lib', 'OauthLib.Consumer');
App::import('Lib', 'OauthLib.Signature');
App::import('Lib', 'OauthLib.ConsumerToken');
App::import('Controller', 'OauthLib.OauthAppController');
require_once APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'library' . DS . 'uri.php';

/**
 * Oauth Test case.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class ClientHttpGoogleTest extends CakeTestCase {

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
 * testStepByStepTokenRequest
 *
 * @return void
 */
	public function testStepByStepTokenRequest() {
		$this->consumer = new Consumer('weitu.googlepages.com', 'secret');
		$this->ConsumerToken = new ConsumerToken($this->consumer, 'token_411a7f', '3196ffd991c8ebdb');
		$requestUri = & new URI('https://www.google.com/accounts/OAuthGetRequestToken?scope=https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds');
		$nonce = 'fa95f7d3-8ff0-4dd6-b7f1-49a691ec34ca';
		$timestamp = time();
		$config = array('host' => 'www.google.com', 'scheme'  => 'https', 'request' => array('uri' => array('scheme'  => 'https', 'host' => 'www.google.com')));
		$http = new HttpSocket($config);
		$token = null;
		
		$requestParams = array('scheme'  =>  'header', 'nonce'  =>  $nonce, 'timestamp'  =>  $timestamp, 'signature_method'  => 'RSA-SHA1', 'parameters' =>  array( 'scope' => 'https://www.google.com/m8/feeds'));
		$privateFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.pem');
		$publicFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.cer');
		$requestParams['privateCert'] = $privateFile->read();
		$requestParams['privateCertPass'] = '';
		$requestParams['publicCert'] = $publicFile->read();
		$request = & new ClientHttp($http, $requestUri->path . $requestUri->queryWithQ());

		$signatureBaseString = $request->signatureBaseString($http, $this->consumer, $token, $requestParams);

		$this->assertEqual("GET&https%3A%2F%2Fwww.google.com%2Faccounts%2FOAuthGetRequestToken&oauth_consumer_key%3Dweitu.googlepages.com%26oauth_nonce%3D{$nonce}%26oauth_signature_method%3DRSA-SHA1%26oauth_timestamp%3D{$timestamp}%26oauth_version%3D1.0%26scope%3Dhttps%253A%252F%252Fwww.google.com%252Fm8%252Ffeeds", $signatureBaseString);

		$request->oauth($http, $this->consumer, $token, $requestParams);
		$this->assertEqual('GET', $request->method);

		$response = $request->request();
		$this->assertEqual("200", $response['status']['code']);
		$this->assertEqual("oauth_token=", substr($response['body'], 0, 12));
	}

/**
 * requestParametersToS
 *
 * @return void
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
 * @return void
 */
	public function sorting($data) {
		$arr = explode('&', $data);
		sort($arr);
		return implode('&', $arr);
	}
}
