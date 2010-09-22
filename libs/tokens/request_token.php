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
App::import('Lib', 'OauthLib.AccessToken');

/**
 * The RequestToken is used for the initial Request.
 * This is normally created by the Consumer object.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs.tokens
 */
class RequestToken extends ConsumerToken {

/**
 * Returns the authorization url that you need to use for redirecting the user
 *
 * @return string
 */
	public function authorizeUrl($params = array()) {
		$params = array_merge((array)$params, array('oauth_token' => $this->token));
		return $this->_buildAuthorizeUrl($this->consumer->authorizeUrl(), $params);
	}

/**
 * Returns the authorization url that you need to use for redirecting the user
 *
 * @return string
 */
	public function isCallbackConfirmed() {
		return !empty($this->params['oauth_callback_confirmed']) && $this->params['oauth_callback_confirmed'] == 'true';
	}

/**
 * Exchange for AccessToken on server
 *
 * @param array $options
 * @param array $params, for example header can passed here
 * @return boolean
 */
	public function getAccessToken($options = array(), $params = array()) {
		$response = $this->consumer->tokenRequest($this->consumer->httpMethod(), $this->consumer->accessTokenPath(), $this, $options, $params);
		return new AccessToken($this->consumer, $response['oauth_token'], $response['oauth_token_secret']);
	}

/**
 * construct an authorization url
 *
 * @param string $baseUrl
 * @param array $params
 * @return boolean
 */
    protected function _buildAuthorizeUrl($baseUrl, $params) {
		$uri = OauthHelper::parseUri($baseUrl);
		if (!isset($uri['query'])) {
			$uri['query'] = array();
		}
		$uri['query'] = array_merge($uri['query'], $params);
		return OauthHelper::buildUri($uri);
    }	
}
