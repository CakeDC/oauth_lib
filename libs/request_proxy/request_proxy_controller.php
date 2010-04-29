<?php

RequestFactory::register('OauthLibAppController', 'RequestProxyController');
RequestFactory::register('OauthAppController', 'RequestProxyController');
RequestFactory::register('AppController', 'RequestProxyController');
if (!class_exists('OauthHelper')) {
	App::import('Lib', 'OauthLib.OauthHelper');
}

class RequestProxyController extends RequestProxyBase {
/**
 * Request Object
 *
 * @var Object $request
 * @access public
 */
	public $request;
/**
 * Configuaration options
 *
 * @var array $options
 * @access public
 */
	public $options;
/**
 * Constructor
 *
 * @param Object $request
 * @param array $options
 * @access public
 */
	public function __construct(&$request, $options = array()) {
		parent::__construct($request, $options);
	}
/**
 * Get request method
 *
 * @return string
 * @access public
 */
	public function method() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
/**
 * Get request uri
 *
 * @return string
 * @access public
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
 * @access public
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
 * @access private
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
 * @access private
 */
	private function __requestParams() {
		if (empty($this->request->data)) {
			return array();
		}
		return $this->request->data;
	}

}
?>