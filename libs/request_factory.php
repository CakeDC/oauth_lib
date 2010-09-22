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
 * Request factory used to proxy requests to real object that provide request info.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */
class RequestFactory {

/**
 *  List of registered proxies
 *
 * @var array $availableProxies
 */
	public $availableProxies = array();

/**
 * Singleton constructor
 *
 * @return RequestFactory instance
 */
	public function &getInstance() {
		static $instance = array();
		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] = new RequestFactory();
		}
		return $instance[0];
	}

/**
 * Factory register object method
 *
 * @param string $proxy
 * @param string $class
 */
	public function register($proxy, $class) {	
		$_this = RequestFactory::getInstance();
		$_this->availableProxies[$proxy] = $class;
	}

/**
 * Wrap request class with proxy
 *
 * @param Request $request
 * @param string $options
 * @return RequestProxy
 */
	public function proxy(&$request, $options = array()) {
		$_this = RequestFactory::getInstance();
		if (is_object($request) && in_array(get_class($request), $_this->availableProxies)) {
			return $request;
		}
		if (isset($_this->availableProxies[get_class($request)])) {
			$class = $_this->availableProxies[get_class($request)];
			return new $class($request, $options);
		}
		foreach ($_this->availableProxies as $requestClass => $proxyClass) {
			if (is_subclass_of($request, $requestClass)) {
				return new $proxyClass($request, $options);
			}
		}
		throw new Exception("UnknownRequestType " . get_class($request));
		return false;
	}
}

if (!class_exists('RequestProxyBase')) {
	App::import('Lib', 'OauthLib.RequestProxyBase');
}
if (!class_exists('RequestProxyController')) {
	App::import('Lib', 'OauthLib.RequestProxyController');
}
if (!class_exists('RequestProxyHttp')) {
	App::import('Lib', 'OauthLib.RequestProxyHttp');
}
if (!class_exists('RequestProxyMock')) {
	App::import('Lib', 'OauthLib.RequestProxyMock');
}
