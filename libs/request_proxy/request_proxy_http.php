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

if (!class_exists('HttpSocket')) {
	//App::import('Core', 'HttpSocket');
	App::import('Vendor', 'OauthLib.HttpSocket');
}
if (!class_exists('ClientHttp')) {
	App::import('Lib', 'OauthLib.ClientHttp');
}
RequestFactory::register('ClientHttp', 'RequestProxyHttp');

/**
 * Request proxy http class. Provide access to request generated using client http library.
 * 
 * @package oauth_lib
 * @subpackage oauth_lib.libs.request_proxy
 */
class RequestProxyHttp extends RequestProxyBase {

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
 * Constructor
 *
 * @param Object $request
 * @param array $options
 */
	public function __construct(&$request, $options = array()) {
		parent::__construct($request, $options);
	}

/**
 * Get request method
 *
 * @return string
 */
	public function method() {
		return $this->request->method;
	}

/**
 * Get request uri
 *
 * @return string
 */
	public function uri() {
		$uri = $this->options['uri'];
		return OauthHelper::getBaseUri($uri);
	}

/**
 * Get request parameter 
 *
 * @return array
 */
	public function parameters() {
		if (isset($this->options['clobber_request']) && $this->options['clobber_request']) {
			$params = $this->options['parameters'];
		} else {
			$params = $this->__allParameters();
		}
		ksort($params);
		return $params;
	}

/**
 * Gather list of all parameters
 *
 * @return array
 */
	private function __allParameters() {
		$query = $this->__queryString();
		if (substr($query, 0, 1) == '?') {
			$query = substr($query, 1);
		}
		$requestParams = HttpSocket::parseQuery($query);
		foreach($requestParams as $k => $v) {
			if (!is_array($requestParams[$k])) {
				$requestParams[$k] = array($requestParams[$k]);
			}
		}
		if (isset($this->options['parameters'])) {
			foreach($this->options['parameters'] as $k => $v) {
				if (isset($requestParams[$k])) {
					if (is_array($requestParams[$k])) {
						$requestParams[$k][] = $v;
					} else {
						$requestParams[$k] = Set::flatten(array($requestParams[$k], $v));
					}
				} else {
					if (!is_array($v)) {
						$v = array($v);
					}
					$requestParams[$k] = Set::flatten($v);
				}
			}
		}
		return $requestParams;
	}

/**
 * Generate query string
 *
 * @return string
 */
	private function __queryString() {
		$data = '';
		$queryParams = $this->__queryParams();
		if ($queryParams != '') {
			if ($data != '') {
				$data .= '&';
			}
			$data .= $queryParams;
		}
		
		$authHeaderParams = $this->__authHeaderParams();
		if ($authHeaderParams != '') {
			if ($data != '') {
				$data .= '&';
			}
			$data .= $authHeaderParams;
		}
		
		$isFormUrlEncoded = !empty($this->request->sock->config['request']['header']['Content-Type']) && strtolower($this->request->sock->config['request']['header']['Content-Type']) == 'application/x-www-form-urlencoded';
		$postParams = $this->__postParams();
		if ($postParams != '' && strtoupper($this->method()) == 'POST' && $isFormUrlEncoded) {
			if ($data != '') {
				$data .= '&';
			}
			$data .= $postParams;
		}
		
		return $data;
	}

/**
 * Fetch query parameters
 *
 * @return string
 */
	private function __queryParams() {
		$url = $this->request->query();
		if (strlen($url)>0) {
			$url = "?$url";
		}
		return $url;
		if (isset($this->request->sock->config['request']['uri']['query'])) {
			$qParams = $this->request->sock->config['request']['uri']['query'];
			if (is_array($qParams) && count($qParams)>0) {
				$url = '?' . OauthHelper::mapper($qParams, '&', '');
			} elseif (is_string($qParams)) {
				$url = $qParams;
			} else {
				$url = '?';
			}
			return $url;
		} else {
			return '';
		}
	}

/**
 * Fetch post parameters
 *
 * @return string
 */
	private function __postParams() {
		return $this->request->body();
	}

/**
 * Fetch header parameters
 *
 * @return string
 */
	private function __authHeaderParams() {
		if (!isset($this->request->authorization) || !substr($this->request->authorization, 0, 5) == 'OAuth') {
			return null;
		}
		$this->auth_params = $this->request->authorization;
		return $this->auth_params;
	}
}
