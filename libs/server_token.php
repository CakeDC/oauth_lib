<?php

App::import('Lib', 'OauthLib.OauthHelper');
App::import('Behavior', 'OauthLib.TokenBehavior');

/**
 * Used on the server for generating tokens
 */
class ServerTokenBehavior extends ModelBehavior {
/**
 * Constructor
 *
 * @access public
 */
	public function __construct() {
		parent::__construct(OauthHelper::generateKey(16), OauthHelper::generateKey());
	}
}

?>