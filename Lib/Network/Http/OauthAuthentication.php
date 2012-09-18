<?php
/**
 * Oauth authentication
 *
 * Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Consumer', 'OauthLib.Lib');
App::uses('HttpClient', 'OauthLib.Lib');
App::uses('AccessToken', 'OauthLib.Lib');
 
/**
 * Oauth authentication
 *
 * @package       OauthLib.Network.Http
 */
class OauthAuthentication {

	protected static $_requiredKeys = array(
		'Consumer' => array(
			'consumer_token', 
			'consumer_secret'), 
		'Token' => array(
			'token',
			'secret'));

/**
 * Authentication
 *
 * @param HttpSocket $http
 * @param array $authInfo
 * @return void
 */
	public static function authentication(HttpSocket $http, &$authInfo) {
		if (!self::_oauthInfoProvided($authInfo, self::$_requiredKeys)) {
			return;
		}
		self::_signRequest($http, $authInfo);
	}

/**
 * Validate authentication config
 *
 * @param HttpSocket $http
 * @param array $authInfo
 * @return boolean
 */
		protected static function _oauthInfoProvided($authInfo, $required) {
			foreach ($required as $k => $v) {
				if (is_array($v) && !self::_oauthInfoProvided($authInfo[$k], $v)) {
					return false;
				}
				if (!is_array($v) && !array_key_exists($v, $authInfo)) {
					return false;
				}
			}
			return true;
		}


/**
 * Sign oauth request
 *
 * @param HttpSocket $http
 * @param array $authInfo
 */
		protected static function _signRequest($http, &$authInfo) {
            $url = $http->url($http->request['uri']);
			$consumer = new Consumer($authInfo['Consumer']['consumer_token'], $authInfo['Consumer']['consumer_secret'], $authInfo);
            $consumer->http = $http;
			$request = new ClientHttp($http,  $url, array(), $http->request['method']);
			$data = $http->request['body'];
			if (is_array($data)) {
				$request->setFormData($data);
			} elseif (!empty($data)) {
				$request->body($data);
			}
			
			$token = new AccessToken($consumer, $authInfo['Token']['token'], $authInfo['Token']['secret']);
			$consumer->sign($request, $token, $authInfo);

			if (!empty($request->authorization)) {
				$http->request['header']['Authorization'] = $request->authorization;
				$http->request['header']['HTTP_AUTHORIZATION'] = $request->authorization;
				$http->request['header']['X-HTTP_AUTHORIZATION'] = $request->authorization;
			}
		}

}
