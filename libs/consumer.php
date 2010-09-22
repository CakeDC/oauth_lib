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
	App::import('Vendor', 'OauthLib.HttpSocket');
}

App::import('Model', 'OauthLib.Socket');
App::import('Lib', 'OauthLib.ClientHttp');
App::import('Lib', 'OauthLib.OauthHelper');
App::import('Lib', 'OauthLib.AccessToken');
App::import('Lib', 'OauthLib.RequestToken');

/**
 * FailRequestException
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */
class FailRequestException extends Exception {
}

/**
 * CakePHP Oauth library consumer implementation.
 *
 * It provides set of methods to use in combine with Cakephp Auth component to authenticate users
 * with remote auth servers like twitter.com, so users will have transparent authentication later.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */
class Consumer {

/**
 * consumer key
 *
 * @var string
 */
	public $key;

/**
 * consumer secret key
 *
 * @var string
 */
	public $secret;

/**
 * 'oauth_signature_method', Signature method used by server. Defaults to HMAC-SHA1
 * 'request_token_uri', default paths on site. These are the same as the defaults set up by the generators
 * 'scheme',
 *  Possible values:
 *    'header' - via the Authorize header (Default)
 *    'body' - url form encoded in body of POST request
 *    'query_string' - via the query part of the url
 * 'http_method', Default http method used for OAuth Token Requests (defaults to 'post')
 *
 * @var array
 */
	private $__defaultOptions = array(
		'signature_method' => 'HMAC-SHA1',
		'request_token_uri' => '/oauth/request_token',
		'authorize_uri' => '/oauth/authorize',
		'access_token_uri' => '/oauth/access_token',
		'scheme' => 'header',
		'http_method' => 'POST',
		'oauth_version' => "1.0");

/**
 * Site location
 *
 * @var string
 */
	public $site;

/**
 * Consumer options
 *
 * @var array $options
 */
	public $options = array();

/**
 * Http socket instance
 *
 * @var HttpSocket
 */
	public $http;

/**
 * Http request method
 *
 * @var string
 */
	public $httpMethod;

/**
 * Consumer constructor
 *
 * @param string $consumerKey
 * @param string $consumerSecret
 * @param array $options
 */
	public function __construct($consumerKey, $consumerSecret, $options = array()) {
		$this->initConsumer($consumerKey, $consumerSecret, $options);
	}

/**
 * Inintialize a new consumer instance by passing it a configuration hash:
 *
 * Start the process by requesting a token
 *
 *   $request_token = $consumer->getRequestToken();
 *   $this->Session->write(request_token] = $request_token;
 *   $this->redirect($request_token->authorizeUrl());
 *
 * When user returns create an access_token
 *
 *   $accessToken = $this->requestToken->getAccessToken();
 *   @photos = $accessToken->get('/photos.xml');
 *
 * @param string $consumerKey
 * @param string $consumerSecret
 * @param array $options
 */
	public function init($consumerKey, $consumerSecret, $options = array()) {
		return $this->initConsumer($consumerKey, $consumerSecret, $options);
	}

	public function initConsumer($consumerKey, $consumerSecret, $options = array()) {
		$this->options = array_merge($this->__defaultOptions, $options);
		OauthHelper::log($options);
		OauthHelper::log($this->options);
		$this->key = $consumerKey;
		$this->secret = $consumerSecret;
	}

/**
 * The default http method
 *
 * @param AppModel $model
 * @return string
 */
	public function httpMethod() {
		if (isset($this->options['http_method'])) {
			return strtoupper($this->options['http_method']);
		} else {
			return 'POST';
		}
	}

/**
 * The HTTP object for the site
 *
 * @return HttpSocket
 */
	public function http() {
		if (!empty($this->http)) {
			return $this->http;
		}
		$url = $this->site();
		$this->http = & new HttpSocket();
		$this->http->configUri($url);
		if ($this->http->config['request']['uri']['scheme'] != 'http') {
			$this->http->config['scheme'] = $this->http->config['request']['uri']['scheme'];
		}
		return $this->http;
	}

/**
 * Makes a request to the service for a new OAuthRequestToken
 *
 * if oauth_callback wasn't provided, it is assumed that oauth_verifiers
 * will be exchanged out of band
 *
 * If request tokens are passed between the consumer and the provider out of
 * band (i.e. callbacks cannot be used), need to use "oob" string per section 6.1.1
 *
 * @param array $requestOptions
 * @param array $params
 * @return boolean
 */
	public function getRequestToken($requestOptions = array(), $params = array()) {
		$token = null;
		if (!isset($requestOptions['oauth_callback'])) {
			$requestOptions['oauth_callback'] = 'oob';
		}
		$response = $this->tokenRequest($this->httpMethod(), $this->requestTokenPath(), $token, $requestOptions, $params);
		if (isset($response['oauth_token']) && isset($response['oauth_token_secret'])) {
			return new RequestToken($this, $response['oauth_token'], $response['oauth_token_secret']);
		} else {
			return false;
		}
	}

/**
 * Create the http request object for a given httpMethod and path
 *
 * @param HttpSocket $socket
 * @param string $httpMethod
 * @param string $path
 * @param array $params
 * @return ClientHttp, Request instanse
 */
	protected function _createHttpRequest(&$socket, $httpMethod, $path, $params = array()) {
		if (isset($params['data'])) {
			$data = $params['data'];
			unset($params['data']);
		} else {
			$data = null;
		}

		if (isset($params['headers'])) {
			$headers = $params['headers'];
			unset($params['headers']);
		} else {
			$headers = $params;
		}

		switch (strtoupper($httpMethod)) {
			case 'POST':
				$request = new ClientHttp($socket, $path, $headers, 'POST');
				//$request->Request->registerCustomHeader('Content-Length: 0'); // Default to 0
			break;
			case 'PUT':
				$request = new ClientHttp($socket, $path, $headers, 'PUT');
				//$request->Request->registerCustomHeader('Content-Length: 0');
			break;
			case 'GET':
				$request = new ClientHttp($socket, $path, $headers, 'GET');
			break;
			case 'DELETE':
				$request = new ClientHttp($socket, $path, $headers, 'DELETE');
			break;
			case 'HEAD':
				$request = new ClientHttp($socket, $path, $headers, 'HEAD');
			break;
			default:
				throw new Exception("Don't know how to handle httpMethod: " . $httpMethod);
			break;
		}
		if (is_array($data)) {
			$request->setFormData($data);
		} elseif (!empty($data)) {
			$request->body($data);
			//$request->Request->registerCustomHeader('Content-Length: ' . strlen($data));
		}
		return $request;
	}

/**
 * Creates and signs an http request.
 * It's recommended to use the Token classes to set this up correctly
 *
 * @param HttpSocket $socket
 * @param string $httpMethod
 * @param string $path
 * @param Token $token
 * @param array $requestOptions
 * @param array $params
 * @return Request
 */
	public function createSignedRequest($socket, $httpMethod, $path, $token = null, $requestOptions = array(), $params = array()) {
		$request = $this->_createHttpRequest($socket, $httpMethod, $path, $params);
		$this->sign($request, $token, $requestOptions);
		return $request;
	}

/**
 * Creates, signs and performs an http request.
 * It's recommended to use the Token classes to set this up correctly.
 * The arguments parameters are a array or string encoded set of parameters if it's a post request as well as optional http headers.
 *
 *   $token = null;
 *   $consumer->request('GET', '/resources', $token, array('scheme' => 'query_string'));
 *   $consumer->request('POST', '/resources', $token, array(), array('data' => $resource, 'Content-Type' => 'application/xml'));
 *
 * @param string $httpMethod
 * @param string $path
 * @param Token $token
 * @param array $requestOptions
 * @param array $params
 * @return
 */
	public function request($httpMethod, $path, $token = null, $requestOptions = array(), $params = array()) {
		$http = $this->http();
		$requestObject = $this->createSignedRequest($http, $httpMethod, $path, $token, $requestOptions, $params);
		return $requestObject->request();
	}

/**
 * Creates a request and parses the result as url_encoded. This is used internally for the RequestToken and AccessToken requests.
 *
 * @param string $httpMethod
 * @param string $path
 * @param Token $token
 * @param array $requestOptions
 * @param array $params
 * @return array
 */
	public function tokenRequest($httpMethod, $path, &$token = null, $requestOptions = array(), $params = array()) {
		$response = $this->request($httpMethod, $path, $token, $requestOptions, $params);
		$code = $response['status']['code'];
		if ($code >= 200 && $code <= 299) {
		//if ($response['status']['code'] == "200") {}
			if (substr($response['body'], 0, 4) == 'Fail') {
				throw new FailRequestException($response['body']);
			}
			$data = explode('&', $response['body']);
			OauthHelper::log($data);
			$result = array();
			foreach($data as $rec) {
				list($key, $value) = split('=', $rec);
				//$result[$key] = $value;
				$result[$key] = OauthHelper::unescape($value);
			}
			$response['status']['success'] = true;
			return $result;
		} elseif ($code >= 300 && $code <= 399) {
			$response['status']['success'] = false;
		} elseif ($code >= 400 && $code <= 499) {
			throw new UnauthorizedException($response);
		} else {
			$response['status']['success'] = false;
		}
		return false;
	}

/**
 * Sign the Request object. Use this if you have an externally generated http request object you want to sign.
 *
 * @param Request $request
 * @param Token $token
 * @param array $requestOptions
 * @return Request
 */
	public function sign(&$request, $token = null, $requestOptions = array()) {
		$options = array_merge($this->options, $requestOptions);
		//$options = array_merge(array('scheme' => $this->scheme()), $requestOptions);
		return $request->oauth($this->http(), $this, $token, $options);
	}

/**
 * Return the signatureBaseString
 *
 * @param Request $request
 * @param Token $token
 * @param array $requestOptions
 * @return string
 */
	public function signatureBaseString($request, $token = null, $requestOptions = array()) {
		$localOptions = array('scheme' => $this->scheme());
		$options = array_merge($localOptions, $requestOptions);
		$options = array_merge($this->options, $requestOptions);
		return $request->signatureBaseString($this->http(), $this, $token, $options);
	}

/**
 * Exchange for AccessToken on server
 *
 * @param RequestToken $requestToken
 * @param array $options
 * @param array $params, for example header can passed here
 * @return boolean
 */
	public function getAccessToken($requestToken, $options = array(), $params = array()) {
		$response = $this->tokenRequest($this->httpMethod(), $this->accessTokenPath(), $requestToken, $options, $params);
		return new AccessToken($this, $response['oauth_token'], $response['oauth_token_secret']);
	}

/**
 * Uri site getter
 *
 * @return string
 */
	public function site() {
		if (isset($this->options['uri'])) {
			return $this->options['uri'];
		}
		return '';
	}

/**
 * Uri site getter
 *
 * @return string
 */
	public function uri($customUri = null) {
		if ($customUri) {
			$this->uri = $customUri;
		} else {
			$this->uri = $this->site();
		}
		return $this->uri;
	}

/**
 * Scheme getter
 *
 */
	public function scheme() {
		return $this->options['scheme'];
	}

/**
 * Request token path
 *
 * @return string
 */
 	public function requestTokenPath() {
		return $this->options['request_token_uri'];
	}

/**
 * Authorize path
 *
 * @return string
 */
	public function authorizePath() {
		return $this->options['authorize_uri'];
	}

/**
 * Access token path
 *
 * @return string
 */
	public function accessTokenPath() {
		return $this->options['access_token_uri'];
	}

/**
 * Request token url
 *
 * @return string
 */
	public function requestTokenUrl() {
		if (isset($this->options['request_token_url'])) {
			return $this->options['request_token_url'];
		} else {
			return $this->site() . $this->requestTokenPath();
		}
	}

/**
 * Authorize url
 *
 * @return string
 */
	public function authorizeUrl() {
		if (isset($this->options['authorize_url'])) {
			return $this->options['authorize_url'];
	} else {
			return $this->site() . $this->authorizePath();
		}
	}

/**
 * Access token url
 *
 * @return string
 */
	public function accessTokenUrl() {
		if (isset($this->options['access_token_url'])) {
			return $this->options['access_token_url'];
		} else {
			return $this->site() . $this->accessTokenPath();
		}
	}
}
