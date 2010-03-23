<?php
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * Converge Application Platform
 *
 * Copyright 2007-2008, Cake Development Corporation
 * 							1785 E. Sahara Avenue, Suite 490-423
 * 							Las Vegas, Nevada 89104
 *
 * You may obtain a copy of the License at:
 * License page: http://projects.cakedc.com/licenses/TBD  TBD
 * Copyright page: http://converge.cakedc.com/copyright/
 *
 * @filesource
 * @copyright		Copyright 2007-2008, Cake Development Corporation
 * @link				http://converge.cakedc.com/ Converge Application Platform
 * @package			converge.plugins
 * @subpackage		converge.plugins.oauth.models
 * @since			Converge v 1.0.0.0
 * @version			$Revision: 1128 $
 * @modifiedby		$LastChangedBy: skie $
 * @lastmodified	$Date: 2009-09-16 12:52:27 +0400 (Ср, 16 сен 2009) $
 * @license			http://projects.cakedc.com/licenses/TBD  TBD
 */
/**
 * Short description for class.
 *
 * @package		converge.plugins
 * @subpackage	converge.plugins.oauth.models
 */

if (!class_exists('RequestFactory')) {
	App::import('Lib', 'OauthLib.RequestFactory');
}
if (!class_exists('Signature')) {
	App::import('Lib', 'OauthLib.Signature');
}
if (!class_exists('OauthHelper')) {
	App::import('Lib', 'OauthLib.OauthHelper');
}
if (!class_exists('HttpSocket')) {
	//App::import('Core', 'HttpSocket');
	App::import('Vendor', 'OauthLib.HttpSocket');
}


class ClientHelper {

/**
 * Request object: e.g. ClientHttp 
 *
 * @var Request $request
 * @access public
 */
	public $request = null;
/**
 * Optional oauth parameters
 *
 * @var array $options
 * @access public
 */
	public $options = array();
/**
 * Constructor
 *
 * @param Request $request
 * @param array $options
 * @access public
 */
	public function __construct(&$request, $options = array()) {
		$this->request = $request;
		$this->options = $options;
		if (isset($this->options['consumer'])) {
			$consumer = $this->options['consumer'];
			foreach (array('signature_method' => 'oauth_signature_method', 'publicCert' => 'public_certificate', 'privateCert' => 'private_key', 'privateCertPass' => 'private_key_passwd') as $field => $key) {
				if (!isset($this->options[$field]) && isset($consumer->options[$key])) {
					$this->options[$field] = $consumer->options[$key];
				}
			}
		}
		if (!isset($this->options['signature_method'])) {
			$this->options['signature_method'] = 'HMAC-SHA1';
		}
	}
/**
 * Get nonce and generate it if not present 
 *
 * @return string
 * @access public
 */
	public function nonce() {
		if (!isset($this->options['nonce'])) {
			$this->options['nonce'] = OauthHelper::generateKey();
		}
		return $this->options['nonce'];
	}
/**
 * Get timestamp
 *
 * @return integer
 * @access public
 */
	public function timestamp() {
		if (!isset($this->options['timestamp'])) {
			$this->options['timestamp'] = $this->__generateTimestamp();
		}
		return $this->options['timestamp'];
	}
/**
 * Return current unix time
 * 
 * @return integer
 * @access private
 */
	private function __generateTimestamp() {
		return time();
	}
/**
 * Oauth configuration parameters
 *
 * @return array
 * @access public
 */
	public function oauthParameters() {
		$params = array(
			'oauth_callback' => @$this->options['oauth_callback'],
			'oauth_consumer_key' => $this->options['consumer']->key,
			'oauth_signature_method' => $this->options['signature_method'],
			'oauth_token' => (isset($this->options['token']->token) ? $this->options['token']->token : ''),
			'oauth_timestamp' => $this->timestamp(),
			'oauth_nonce' => $this->nonce(),
			'oauth_verifier' => @$this->options['oauth_verifier'],
			'oauth_version' => '1.0'
			);
		//return $params;
		foreach (array_keys($params) as $param)	{
			if (strlen($params[$param]) == 0) {
				unset($params[$param]);
			}
		}
		return $params;
	}
	
	public function amendUserAgentHeader(&$headers) {
		if (empty($this->oauthUaString)) {
			$this->oauthUaString = "OAuth converge v 1.0.0.0";
		}
		if (!empty($headers['User-Agent'])) {
			$headers['User-Agent'] .= ' ' . $this->oauthUaString;
		} else {
			$headers['User-Agent'] = $this->oauthUaString;
		}
	}
	
/**
 * Set parameters method for RSA request method
 *
 * @param array $options
 * @access private
 */
	private function __updateExtraOption(&$options) {
		foreach (array('publicCert', 'privateCert', 'privateCertPass') as $field) {
			if (isset($this->options[$field])) {
				$options[$field] = $this->options[$field];
			}
		}
	}	
/**
 * Get request signature 
 *
 * @param array $extraOptions
 * @return string
 * @access public
 */
	public function signature($extraOptions = array()) {
		$options = array(
			'uri' => $this->options['request_uri'], 
			'consumer' => $this->options['consumer'], 
			'token' => $this->options['token']
			);
		$this->__updateExtraOption($extraOptions);
		$options = array_merge($options, $extraOptions);
		return Signature::sign($this->request, $options);
	}
/**
 * Signature base string  
 *
 * @param array $extraOptions
 * @return string
 * @access public
 */
	public function signatureBaseString($extraOptions = array()) {
		$options = array('uri' => $this->options['request_uri'], 'consumer' => $this->options['consumer'], 'token' => $this->options['token'], 'parameters' => $this->oauthParameters());		
		$this->__updateExtraOption($extraOptions);
		$options = Set::merge($options, $extraOptions);
		return Signature::signatureBaseString($this->request, $options);
	}
/**
 * Generate oauth header 
 *
 * @return string
 * @access public
 */
	public function header() {
		$parameters = $this->oauthParameters();
		// $options = array_merge($this->options, array('parameters' => $parameters));
		$options = array('parameters' => $parameters);
		//debug($options);
		$sig = $this->signature($options);
		if (!empty($sig)) {
			$version = $parameters['oauth_version'];
			unset($parameters['oauth_version']);
			$default_options = array('oauth_signature' => $sig, 'oauth_version' => $version);
			$parameters = array_merge($parameters, $default_options);
		}
		$headerParamsStr = OauthHelper::mapper($parameters, ', ');
		if (isset($this->options['realm'])) {
			$realm = 'realm="' . $this->options['realm'] . '", ';
		} else {
			$realm = '';
		}
		return 'OAuth ' . $realm . $headerParamsStr;
	}
/**
 * Get request parameters
 *
 * @return array
 * @access public
 */
	public function parameters() {
		$proxy = RequestFactory::proxy($this->request);
		return $proxy->parameters();
	}
/**
 * Get request parameters with oauth paramethers
 *
 * @return array
 * @access public
 */
	public function parametersWithOauth() {
		return array_merge($this->oauthParameters(), $this->parameters());
	}
}
 
class ClientHttp {
/**
 * Path value 
 *
 * @var string path
 * @access public
 */
	public $path = '';
/**
 * Authrization string
 * 
 * @var string $authorization
 */
	public $authorization = null;
/**
 * Model asociated db table
 * 
 * @var mixed $useTable
 * @access public
 */
 	public $useTable = false;
/**
 * HTTP method
 *
 * @var string $method
 * @access public
 */
	public $method;
/**
 * Request body
 *
 * @var string $body
 * @access public
 */
	public $body;
/**
 * instance of the URI class
 *
 * @var URI
 * @access public
 */
	public $URI;
 /**
  * Http socket
  * 
  * @var HttpSocket $sock
  * @access public
  */
	public $sock;
/**
 * Constructor
 * 
 * @param HttpSocket $socket
 * @param mixed $url
 * @param array $headers, Http headers
 * @param string $method, Http method
 * @return void
 */
	function __construct($socket, $url, $headers = array(), $method = 'GET') {
		if (is_object($url) && get_class($url) == 'URI') {
			$url = $url->toString();
		}
		if (!is_null($socket)) {
			$this->sockUri = $socket;
		} else {
			$this->sockUri = & new HttpSocket;
		}
		$this->sock = & new HttpSocket;
		$this->sock->config['request']['uri'] = $this->parseUri($url);
		$this->sock->config['request']['header'] = $headers;
		$this->sockUri->config['request']['uri'] = $this->parseUri($url);
		$method = strtoupper($method);
		$this->method = $method;
		$this->updateURI($url);
		$this->setMethod($method);
	}
/**
 * Set request method
 *
 * @param string $method
 * @access public
 */
	public function setMethod($method = null) {
		if (in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'HEAD'))) {
			$this->method = $method;
		}
	}
/**
 * Update uri 
 *
 * @param string $url
 * @access public
 */
	public function updateURI($url = null) {
		if (!empty($url)) {
			$this->sock->configUri($this->parseUri($url));
		}
	}
/**
 * Accessor to request body (post vars)
 *
 * @return post vars
 * @access public
 */
	public function body($body = null) {
		if (!empty($body)) {
			$this->body = $body;
		}
		return $this->body;
	}
/**
 * Add a variable to the  POST request
 *
 * @param string $var
 * @param string $varValue
 * @access private
 */
	private function __addPostVar($var, $varValue = '') {
		if (is_array($varValue)) {
			$varValue = implode('+', $varValue);
		}
		if (!$var || !is_string($var) || !is_scalar($varValue)) {
			throw new Exception('Wrong POST var format');
		}
		if (!empty($this->body)) {
			$this->body .= '&';
		}
		$this->body .= rawurlencode($var) . '=' . rawurlencode($varValue);
	}
/**
 * Add set of post parameters
 *
 * @param array $params
 * @param string $sep
 * @access public
 */
	public function setFormData($params, $sep = '&') {
		$this->sock->config['request']['header']['Content-Type'] = 'application/x-www-form-urlencoded';
		$this->body = '';
		foreach($params as $k => $v) {
			$this->__addPostVar($k, $v);
		}
	}
/**
 * Request wrapper to make local reference with real http server settings
 *
 * @param Object $request
 * @return HttpSocket response
 */
	public function request($request = null) {
		$cfg = $this->sock->config;
		if (empty($request)) {
			$request = $this;
		}

		$this->sock->config['request']['uri']['host'] = $request->sockUri->config['host'];
		//$this->sock->config['request']['uri']['port'] = $request->sockUri->config['port'];
		if (isset($request->sockUri->config['scheme'])) {
			$this->sock->config['request']['uri']['scheme'] = $request->sockUri->config['scheme'];
		}
		if ($this->sock->config['request']['uri']['scheme'] == 'https') {
			$this->sock->config['request']['uri']['port'] = 443;
		}
		$body = $this->body();
		$query = array(
			'uri' => $this->sock->config['request']['uri'],
			'method' => $request->method,
			'body' => $this->body(),
			'header' => array(
				'Connection' => 'close',
				'User-Agent' => 'CakePHP',
				'Authorization' => $request->authorization,
				'HTTP_AUTHORIZATION' => $request->authorization,
				'X-HTTP_AUTHORIZATION' => $request->authorization,
			),
		);
		if (empty($body) && (in_array($request->method, array('POST', 'PUT')))) {
			$query['header']['Content-Length'] = 0; 
		}
		//OauthHelper::log(array('socket::query' => $query));
		$response = $this->sock->request($query);
		//OauthHelper::log(array('socket::response' => $this->sock->response));
		return $this->sock->response;
	}
/**
 * Extract path from uri.
 *
 * @param array $uri
 * @return unknown
 * @access public
 */
	public function path($uri = null) {
		if (!empty($uri)) {
			$this->sock->config['request']['uri']['path'] = $uri['path'];
			$this->sock->config['request']['uri']['query'] = $uri['query'];
		}
		return $this->sock->config['request']['uri']['path'];
	}
/**
 * Return local part of uri
 *
 * @return string
 * @access public
 */	
	public function localPath() {
		$glUri = $this->parseUri($this->sock->buildUri($this->sock->config['request']['uri']));
		return $this->sock->buildUri($glUri, '/%path?%query');
	}
/**
 * Configure oauth for request
 *
 * @param HttpSocket $http
 * @param ConsumerObject $consumer
 * @param TokenObject $token
 * @param array $options
 * @return void
 */
	public function oauth(&$http, &$consumer = null, &$token = null, $options = array()) {
		$default = array('request_uri' => $this->__oauthFullRequestUri($http), 
			'consumer' => $consumer, 
			'token' => $token, 
			'scheme' => 'header', 
			'signature_method' => null, 
			'nonce' => null, 
			'timestamp' => null);
		$options = array_merge($default, (array)$options);
		$this->oauthHelper = new ClientHelper($this, $options);
		$method = "__setOAuth" . Inflector::camelize($options['scheme']);
		return $this->{$method}();
	}
/**
 * Build base signature string for request
 *
 * @param HttpSocket $http
 * @param ConsumerObject $consumer
 * @param TokenObject $token
 * @param array $options
 * @param array $params
 * @return string
 */
	public function signatureBaseString(&$http, &$consumer = null, &$token = null, $options = array(), $params = array()) {
		$default = array('request_uri' => $this->__oauthFullRequestUri($http), 
			'consumer' => $consumer, 
			'token' => $token, 
			'scheme' => 'header', 
			'signature_method' => null, 
			'nonce' => null, 
			'timestamp' => null);
		$options = array_merge($default, $options);
		$this->oauthHelper = new ClientHelper($this, $options);
		return $this->oauthHelper->signatureBaseString($params);
	}
/**
 * Generate signed request uri
 *
 * @param HttpSocket $http
 * @return string
 * @access private
 */
	private function __oauthFullRequestUri(&$http) {
		$glUri = $this->parseUri($this->sock->buildUri($this->sock->config['request']['uri']));
		$localPath = $this->sock->buildUri($glUri, '/%path?%query');
		$this->path = $localPath;
		
		$uri = $http->config['request']['uri'];
		$uri['path'] = $glUri['path'];
		if (isset($glUri['query'])) {
			$uri['query'] = $glUri['query'];
		} else {
			unset($uri['query']);
		}
		$uri['host'] = $http->config['host'];
		if ($http->config['port'] != '80') {
			$uri['port'] = $http->config['port'];
		} 

		if (!empty($http->config['scheme'])) {
			$uri['scheme'] = $http->config['scheme'];
		}
		$url = $this->sock->buildUri($uri);
		//OauthHelper::log($http->config['request']['uri']);
		//OauthHelper::log($uri);
		//OauthHelper::log($url);
		return $url;
	}
/**
 * Set oauth request to header
 * 
 * @return void
 * @access private 
 */
	private function __setOAuthHeader() {
		$this->authorization = $this->oauthHelper->header();
	}
/**
 * Set oauth request to body 
 * 
 * @return void
 * @access private
 */
	private function __setOAuthBody() {
		$this->setFormData($this->oauthHelper->parametersWithOauth());
		$paramsWithSig = array_merge($this->oauthHelper->parameters(), array('oauth_signature' => $this->oauthHelper->signature()));
		$this->setFormData($paramsWithSig);
	}
/**
 * Set oauth request to query string
 *
 * @return void
 * @access private
 */
	private function __setOAuthQueryString() {
		$oauthParamsStr = OauthHelper::mapper($this->oauthHelper->oauthParameters(), "&", '');
		
		$uri = $this->sock->parseUri($this->path);
		if (!isset($uri['query']) || $uri['query'] == '' || count($uri['query']) == 0) {
			$uri['query'] = $oauthParamsStr;
		} else {
			$uri['query'] = OauthHelper::mapper($uri['query'], "&", '') . "&" . $oauthParamsStr;
		}
		
		$this->path($uri);
		$signature = "&oauth_signature=" . OauthHelper::escape($this->oauthHelper->signature());
		$this->sock->config['request']['uri']['query'] .= $signature;
		$this->query = $this->sock->config['request']['uri']['query'];
		//OauthHelper::log('setOAuthQueryString::config' . $this->sock->config);
		//OauthHelper::log('setOAuthQueryString::query' . $this->query);
		//$this->sockUri->config['request']['uri']['query'] .= $signature;
	}

/**
 * Parse uri wrapper. Handle both relative and global uri
 *
 * @param string $uri
 * @return string
 * @access public
 */
	public function parseUri($uri) {
		$sock = new HttpSocket;
		$type = 'FULL';

		if (strpos($uri, '://') === false) {
			$localUri = 'http://sample.com' . $uri;
			$uri = 'http://' . $uri;
		}

		$uriArray = @$sock->parseUri($uri);
		if (!$uriArray) {
			$uriArray = @$sock->parseUri($localUri);
			if (!$uriArray) {
				return false;
			} else {
				$type = 'REL';
			}
		}
		return $uriArray;
	}

/**
 * Return query uri based on request configuration
 *
 * @return string
 * @access public
 */
	public function query() {
		if (isset($this->sock->config['request']['uri']['query'])) {
			$qParams = $this->sock->config['request']['uri']['query'];
			if (is_array($qParams) && count($qParams)>0) {
				$url = '' . OauthHelper::mapper($qParams, '&', '');
			} elseif (is_string($qParams)) {
				$url = $qParams;
			} else {
				$url = '';
			}
			return $url;
		}
		return '';
	}

}
?>