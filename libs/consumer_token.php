<?php

App::import('Lib', 'OauthLib.OauthHelper');
App::import('Lib', 'OauthLib.ClientHttp');
App::import('Lib', 'OauthLib.Token');

/**
 * Superclass for tokens used by OAuth Clients
 */
class ConsumerToken extends Token {

/**
 * Consumer Object instance
 *
 * @var Consumer
 * @access public
 */
	public $consumer;
	
/**
 * Constructor
 *
 * @access public
 */
	public function __construct($consumer, $token, $secret, $params = array()) {
		parent::__construct($token, $secret, $params);
		$this->consumer = $consumer;
	}	

/**
 * Make a signed request using given httpMethod to the path
 *
 *   $token->request('GET','/people');
 *   $token->request('POST','/people',$person,array('Content-Type' => 'application/xml' ));
 *
 * @param string $httpMethod
 * @param string $path
 * @param array $headers
 * @param array $params
 * @param array $requestOptions
 * @return string
 */
	public function request($httpMethod, $path, $headers = array(), $params = array(), $requestOptions = array()) {
		$params['headers'] = $headers;
		//debug($httpMethod);
		$this->response = $this->consumer->request($httpMethod, $path, $this, $requestOptions, $params);
		return $this->response;
	}
/**
 * Sign a request generated elsewhere using ClientHttp
 *
 * @param Request $request
 * @param array $options
 * @return unknown
 */
	public function sign(&$request, $options = array()) {
		return $this->consumer->sign($request, $this, $options);
	}
}

?>