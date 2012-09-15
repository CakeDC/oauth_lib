<?php
/**
 * Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('OauthHelper', 'OauthLib.Lib');
App::import('Behavior', 'OauthLib.TokenBehavior');

/**
 * Used on the server for generating tokens
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs.tokens
 */
class ServerTokenBehavior extends ModelBehavior {

/**
 * Constructor
 *
 */
	public function __construct() {
		parent::__construct(OauthHelper::generateKey(16), OauthHelper::generateKey());
	}
}
