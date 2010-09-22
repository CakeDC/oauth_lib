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

RequestFactory::register('OauthLibAppController', 'RequestProxyController');
RequestFactory::register('OauthAppController', 'RequestProxyController');
RequestFactory::register('AppController', 'RequestProxyController');
if (!class_exists('OauthHelper')) {
	App::import('Lib', 'OauthLib.OauthHelper');
}

/**
 * Request proxy controller class. Provide access to request coming to the controller
 * 
 * @package oauth_lib
 * @subpackage oauth_lib.libs.request_proxy
 */
class RequestProxyController extends RequestProxyBase {

/**
 * Request Object
 *
 * @var Object $request
 */
	public $request;

/**
 * Configuaration options
 *
 * @var array $options
 */
	public $options;

/**
 * Get request method
 *
 * @return string
 */
	public function method() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

/**
 * Get request uri
 *
 * @return string
 */
	public function uri() {
		$uri = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		if (isset($_SERVER['URI'])) {
			$uri = $_SERVER['URI'];
		}
		$protocol = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
			$protocol = 'https';
		}
		$server = $protocol . '://' . $_SERVER['SERVER_NAME'];
		if (!((('80' == $_SERVER['SERVER_PORT']) && ($protocol == 'http')) || (('3128' == $_SERVER['SERVER_PORT']) && ($protocol == 'https')))) {
			$server .= ':' . $_SERVER['SERVER_PORT'];
		}
		$fullUri = $server . $uri;
		return OauthHelper::getBaseUri($fullUri);
	}

/**
 * Get request parameter 
 *
 * @return array
 */
	public function parameters() {
		if (!empty($this->options['clobber_request'])) {
			if (isset($this->options['parameters'])) {
				$params = $this->options['parameters'];
			} else {
				$params = array();
			}
		} else {
			$params = array_merge($this->__requestParams(), $this->__queryParams());
			$params = array_merge($params, $this->headerParams());
			if (isset($this->options['parameters'])) {
				$params = array_merge($params, $this->options['parameters']);
			}
		}
		ksort($params);
		OauthHelper::log($params);
		return $params;
	}

/**
 * Fetch query parameters
 *
 * @return string
 */
	private function __queryParams() {
		$params = $this->request->params['url'];
		unset($params['url']);
		unset($params['ext']);
		return $params;
	}

/**
 * Fetch request parameters
 *
 * @return string
 */
	private function __requestParams() {
		if (empty($this->request->data)) {
			return array();
		}
		return $this->request->data;
	}
}
