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

/**
 * Superclass for the various tokens used by OAuth
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs.tokens
 */
class Token {

/**
 * Token public key
 *
 * @var string
 */
	public $token;

/**
 * Token secret key
 *
 * @var string
 */
	public $tokenSecret;
	
/**
 * Token additional parameter
 *
 * @var mixed
 */
	public $params;

/**
 * Init model instance with token parameters
 *
 * @param AppModel $model
 * @param string $token
 * @param string $secret
 * @param array $params
 */
	public function __construct($token, $secret = null, $params = array()) {
		if (is_array($token)) {
			$params = $token;
			$token = $params['oauth_token'];
			$secret = $params['oauth_token_secret'];
		}
		$this->token = $token;
		$this->tokenSecret = $secret;
		$this->params = $params;
	}

/**
 * Http query request string
 *
 * @param AppModel $model
 * @return string
 */
	public function toQuery(&$model) {
		return "oauth_token=" . OauthHelper::escape($this->token) . "&oauth_secret=".OauthHelper::escape($this->tokenSecret);
	}
}
