<?php

class RequestFactory {
/**
 *  List of registered proxies
 *
 * @var array $availableProxies
 * @access public
 */
	public $availableProxies = array();
/**
 * Constructor
 *
 * @access public
 */
	public function __construct() {
	}
/**
 * Singleton constructor
 *
 * @return RequestFactory instance
 * @access public
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
 * @access public
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
 * @access public
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

?>