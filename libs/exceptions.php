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

/**
 * CakePHP Oauth library generic excetions. 
 * 
 * It provides set of methods to use in combine with Cakephp Auth component to authenticate users
 * with remote auth servers like twitter.com, so users will have transparent authentication later.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */

/**
 * OauthException
 *
 * @package oauth_lib
 * @author oauth_lib.libs
 */
class OauthException extends Exception {
}

/**
 * UnauthorizedException
 *
 * @package oauth_lib
 * @author oauth_lib.libs
 */
class UnauthorizedException extends OauthException {

/**
 * Request
 *
 * @var mixed
 */
    public $request = null;

/**
 * Constructor
 *
 * @param mixed $request 
 */
    public function __construct($request = null) {
		$this->request = $request;
		parent::__construct();
    }

/**
 * Request body
 *
 * @return string
 */
	public function requestBody() {
		return $this->request['body'];
	}

/**
 * To String
 *
 * @return string
 */
    public function __toString() {
		if (!empty($this->request)) {
			return $this->request['status']['code'] . ' ' . $this->request['body'];
		} else {
			return '';
		}
    }
}

/**
 * ProblemException
 *
 * @package oauth_lib
 * @author oauth_lib.libs
 */
class ProblemException extends UnauthorizedException {

/**
 * Problem
 *
 * @var mixed
 */
    public $problem = null;

/**
 * Parameters
 *
 * @var mixed
 */
    public $params = null;

/**
 * Constructor
 *
 * @param string $problem 
 * @param string $request 
 * @param string $params 
 */
    public function __construct($problem, $request = null, $params = array()) {
		parent::__construct($request);
		$this->problem = $problem;
		$this->params = $params;
    }

/**
 * To string
 *
 * @return string
 */
	public function __toString() {
		return $this->problem;
	}
}
