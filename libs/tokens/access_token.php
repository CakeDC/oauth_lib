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

App::import('Lib', 'OauthLib.OauthHelper');
App::import('Lib', 'OauthLib.ConsumerToken');

/**
 * The Access Token is used for the actual "real" web service calls thatyou perform against the server
 *
 * @package 	oauth_lib
 * @subpackage	oauth_lib.libs.tokens
 */
class AccessToken extends ConsumerToken {

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
	public function _request($httpMethod, $path, $headers = array(), $params = array(), $requestOptions = array()) {
		$requestUri = $path;
		$siteUri = $this->consumer->site();
		if ($requestUri != $siteUri) {
			$this->consumer->uri($requestUri);
		}
		$this->response = parent::request($httpMethod, $path, $headers, $params, $requestOptions);
		if ($requestUri != $siteUri) {
			$this->consumer->uri($siteUri);
		}
		return $this->response;
    }
	
/**
 * Make a regular get request using AccessToken
 *
 *   $response=$token->get('/people');
 *   $response=$token->get('/people',array('Accept'=>'application/xml'));
 *
 * @param string $path
 * @param array $headers
 * @param array $params
 * @return array
 */
	public function get($path, $headers = array(), $params = array(), $requestOptions = array()) {
		return $this->request('GET', $path, $headers, $params, $requestOptions);
	}

/**
 * Make a regular head request using AccessToken
 *
 *   $response=$token->head('/people');
 *
 * @param string $path
 * @param array $headers
 * @param array $params
 * @return array
 */
	public function head($path, $headers = array(), $params = array(), $requestOptions = array()) {
		return $this->request('HEAD', $path, $headers, $params, $requestOptions);
	}

/**
 * Make a regular post request using AccessToken
 *
 *   $response=$token->post('/people');
 *   $response=$token->post('/people',array('name'=>'Bob','email=>'bob@mailinator.com'));
 *   $response=$token->post('/people',array('name'=>'Bob','email=>'bob@mailinator.com'),array('Accept'=>'application/xml'));
 *   $response=$token->post('/people',null,array('Accept'=>'application/xml'));
 *   $response=$token->post('/people',$person,array('Accept'=>'application/xml','Content-Type' => 'application/xml'));
 *
 * @param string $path
 * @param array $headers
 * @param array $params
 * @return array
 */
	public function post($path, $body = '', $headers = array(), $params = array(), $requestOptions = array()) {
		$params['data'] = $body;
		return $this->request('POST', $path, $headers, $params, $requestOptions);
	}

/**
 * Make a regular put request using AccessToken
 *
 *   $response=$token->put('/people/123');
 *   $response=$token->put('/people/123',array('name'=>'Bob','email'=>'bob@mailinator.com'));
 *   $response=$token->put('/people/123',array('name'=>'Bob','email'=>'bob@mailinator.com'),array('Accept'=>'application/xml'));
 *   $response=$token->put('/people/123',nil,array('Accept'=>'application/xml'));
 *   $response=$token->put('/people/123',$person,array('Accept'=>'application/xml','Content-Type' => 'application/xml'));
 *
 * @param string $path
 * @param array $headers
 * @param array $params
 * @return array
 */
	public function put($path, $body = '', $headers = array(), $params = array(), $requestOptions = array()) {
		$params['data'] = $body;
		return $this->requestByToken('PUT', $path, $headers, $params, $requestOptions);
	}

/**
 * Make a regular delete request using AccessToken
 *
 *   $response=$token->delete('/people/123');
 *   $response=$token->delete('/people/123',array('Accept'=>'application/xml'));
 *
 * @param string $path
 * @param array $headers
 * @param array $params
 * @return array
 */
	public function delete($path, $headers = array(), $params = array(), $requestOptions = array()) {
		return $this->requestByToken('DELETE', $path, $headers, $params, $requestOptions);
	}
}
