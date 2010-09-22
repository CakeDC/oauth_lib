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
require_once APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'library' . DS . 'uri.php';
App::import('Lib', 'OauthLib.Consumer');
App::import('Lib', 'OauthLib.Signature');
App::import('Lib', 'OauthLib.ConsumerToken');
App::import('Lib', 'OauthLib.ClientHttp');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class ConsumerGoogleTest extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		$this->consumer = new Consumer('consumer_key_86cad9', '5888bf0345e5d237',
			array(
			'uri' => "http://blabla.bla",
			'request_token_uri' => "/oauth/example/request_token.php",
			'access_token_uri' => "/oauth/example/access_token.php",
			'authorize_uri' => "/oauth/example/authorize.php",
			'scheme' => 'header',
			'http_method' => 'get'
			));
		$this->ConsumerToken = new ConsumerToken($this->consumer, 'token_411a7f', '3196ffd991c8ebdb');

		$this->requestUri = new URI('http://example.com/test?key=value');
		$this->requestParameters = array('key'  =>  'value');
		$this->nonce = "225579211881198842005988698334675835446";
		$this->timestamp = "1199645624";
		$config = array('host' => 'example.com', 'request' => array('uri' => array('host' => 'example.com')));
		$this->consumer->http = new HttpSocket($config);
	}

/**
 * _testStepByStepTokenRequest
 *
 * @return void
 */
	public function _testStepByStepTokenRequest() {
		$consumerConfig = array(
	        'uri' => "https://www.google.com",
	        'request_token_uri' => "/accounts/OAuthGetRequestToken?scope=https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds",
	        'access_token_uri' => "/accounts/OAuthGetAccessToken",
	        'authorize_uri' => "/accounts/OAuthAuthorizeToken",
	        'scheme' => 'header'
	        );
		$this->consumer = new Consumer("weitu.googlepages.com", "secret", $consumerConfig);
		
		$this->requestUri = new URI('https://www.google.com/accounts/OAuthGetRequestToken?scope=https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds');
		$config = array('host' => 'www.google.com', 'scheme'  => 'https', 'request' => array('uri' => array('scheme'  => 'https', 'host' => 'www.google.com')));
		$this->consumer->http = new HttpSocket($config);

		$options = array('scheme'  =>  'header', 'nonce'  =>  'nonce', 'timestamp'  =>  time(), 'signature_method'  => 'RSA-SHA1', 'parameters' =>  array( 'scope' => 'https://www.google.com/m8/feeds'));
		$privateFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.pem');
		$publicFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.cer');
		$options['privateCert']     = $privateFile->read();
		$options['publicCert']      = $publicFile->read();
		$options['privateCertPass']      = '';
		//$options['rsa_private']     = $privateFile->read();
		//$options['rsa_certificate'] = $publicFile->read();
		$request = & new ClientHttp($this->consumer->http, $this->requestUri->path . $this->requestUri->queryWithQ());
		
		$signatureBaseString = $this->consumer->signatureBaseString($request, null, $options);
		$this->assertEqual("GET&https%3A%2F%2Fwww.google.com%2Faccounts%2FOAuthGetRequestToken&oauth_consumer_key%3Dweitu.googlepages.com%26oauth_nonce%3D{$options['nonce']}%26oauth_signature_method%3DRSA-SHA1%26oauth_timestamp%3D{$options['timestamp']}%26oauth_token%3D%26oauth_version%3D1.0%26scope%3Dhttps%253A%252F%252Fwww.google.com%252Fm8%252Ffeeds", $signatureBaseString);
		$this->consumer->sign($request, null, $options);
	    $this->assertEqual('GET', $request->method);
	    $this->assertEqual(null, $request->body);
		$http = $this->consumer->http();
		$response = $request->request();
		$this->assertEqual("200",$response['status']['code']);
	}

/**
 * __testLogin
 *
 * @return void
 */
	public function __testLogin() {
		$browser = &new SimpleBrowser();		
		$browser->setMaximumRedirects(5);		
		$browser->setConnectionTimeout(10000);		
		$browser->get('https://www.google.com/accounts/OAuthAuthorizeToken');
		echo "\n\n";
        $browser->setField('Email', 'oauthdotnet@gmail.com');
        $browser->setField('Passwd', 'oauth_password');
        $browser->clickSubmitByName('signIn');
		echo "\n\n";
		if (preg_match('/url=\'(.+)\'/', $browser->getContent(), $matches)) {
		echo "\n\n";
			echo $matches[1];
		echo "\n\n";
			$browser->get($matches[1]);
			debug($browser->getContent());
		} else {
			$this->assertTrue(false);
		}
		return ;
	}

/**
 * testGetTokenSequence
 *
 * @return void
 */
	public function testGetTokenSequence() {
		$consumerConfig = array(
	        'uri' => "https://www.google.com",
	        'request_token_uri' => "/accounts/OAuthGetRequestToken?scope=https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds",
	        'access_token_uri' => "/accounts/OAuthGetAccessToken",
	        'authorize_uri' => "/accounts/OAuthAuthorizeToken",
	        'scheme' => 'header',
	        'oauth_signature_method' => 'RSA-SHA1',
	        'http_method' => 'GET',
	        );
		$privateFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.pem');
		$publicFile = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.cer');
		$consumerConfig['rsa_private']     = $privateFile->read();
		$consumerConfig['rsa_certificate'] = $publicFile->read();
		$this->consumer = new Consumer("weitu.googlepages.com", "secret", $consumerConfig);
		$this->requestUri = new URI('https://www.google.com/accounts/OAuthGetRequestToken?scope=https%3A%2F%2Fwww.google.com%2Fm8%2Ffeeds');
		$config = array('host' => 'www.google.com', 'scheme'  => 'https', 'request' => array('uri' => array('scheme'  => 'https', 'host' => 'www.google.com')));
		$this->consumer->http = new HttpSocket($config);

		$options = array('scheme'  =>  'header', 'nonce'  =>  'nonce', 'timestamp'  =>  time(), 'signature_method'  => 'RSA-SHA1', 'parameters' =>  array( 'scope' => 'https://www.google.com/m8/feeds'));
		$options['privateCert'] = $privateFile->read();
		$options['publicCert'] = $publicFile->read();
		$options['privateCertPass']      = '';

		$options2['privateCert'] = $privateFile->read();
		$options2['publicCert'] = $publicFile->read();
		$options2['privateCertPass']      = '';

		$this->requestToken = $this->consumer->getRequestToken($options);
		$this->assertTrue($this->requestToken);
	}  

/**
 * requestParametersToS
 *
 * @return string
 */
	protected function requestParametersToS() {
		$paramList = array();
		foreach ($this->requestParameters as $k  =>  $v) {
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
