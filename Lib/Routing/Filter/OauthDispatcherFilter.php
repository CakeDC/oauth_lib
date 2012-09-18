<?php
App::uses('DispatcherFilter', 'Routing');
if (!class_exists('Signature')) {
	App::uses('Signature', 'OauthLib.Lib');
}if (!class_exists('RequestProxyController')) {
	App::uses('RequestProxyController', 'OauthLib.Requestproxy');
}
if (!class_exists('OauthHelper')) {
	App::uses('OauthHelper', 'OauthLib.Lib');
}
if (!class_exists('RequestFactory')) {
	App::uses('RequestFactory', 'OauthLib.Lib');
}
if (!class_exists('ClientHttp')) {
	App::uses('ClientHttp', 'OauthLib.Lib');
}
App::uses('ClassRegistry', 'OauthLib.Lib');

/**
 * Copyright 2009-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Oauth Request Filter
 *
 * @package		Plugin.Oauth
 * @subpackage	Plugin.Oauth.Routing.Filter
 */
class OauthDispatcherFilter extends DispatcherFilter {
/**
 * Priority
 *
 * @var integer
 */
	public $priority = 3;


/**
 * Cake Request
 *
 * @var CakeRequest
 */
	public $request;

/**
 * Cake Response
 *
 * @var CakeResponse
 */
	public $response;

/**
 * Parameters show which action need to check with verifyOauthSignature
 *
 * @var array $requireOAuth
 */
   	public $requireOAuth = array(
   		'actions' => array(),
   		'enabled' => false);

/**
 * tokenData, is ServerToken for the request after verifyOauthSignature
 *
 * @var string $tokenData
 */
   	public $tokenData = null;


/**
 * beforeDispatch callback
 *
 * @param CakeEvent $event
 * @return CakeRequest
 */
	public function beforeDispatch($event) {
		$this->request = $event->data['request'];
		$this->response = $event->data['response'];

		if ($this->_isOauthEndpoint($this->request)) {
            if ($this->verifyOauthSignature()) {

            } else {
                $event->stopPropagation();
                exit;
            }
		}
	}

/**
 * Checks if the requested url is the Xhttp endpoint
 *
 * @param CakeRequest $request
 * @return boolean True on success
 */
	protected function _isOauthEndpoint($request) {
		$endpoints = Configure::read('Oauth.endpoints');
		if (empty($endpoints)) {
			$endpoints = array('/oauth/service');
		}

		$url = $request->here;
		if (substr($url, -1) == '/') {
			$url = substr($url, 0, -1);
		}

		foreach ($endpoints as $endpoint) {
			if (strpos($url, $endpoint) === 0) {
				return true;
			}
		}
		return false;
	}

/**
 * Check oauth request signature
 *
 * @return boolean
 */
	public function verifyOauthSignature() {
		$proxy = new RequestProxyController($this);
		$params = $proxy->parameters();
		$token = '';
		if (isset($params['oauth_token'])) {
			$token = $params['oauth_token'];
		}
        App::uses('ClassRegistry', 'Utility');
        $serverRegistry = ClassRegistry::init('OauthServer.ServerRegistry');
		$this->tokenData = $serverRegistry->AccessServerToken->find('first', array(
			'conditions' => array(
			'AccessServerToken.token' => $token,
			'AccessServerToken.authorized' => 1)));
		try {
			$valid = Signature::verify($this, array(
				'consumer_secret' => $this->tokenData['ServerRegistry']['consumer_secret'],
				'token_secret' => $this->tokenData['AccessServerToken']['token_secret']));
		} catch(Exception $e) {
			$valid = false;
		}
		if (!$valid) {
			Configure::write('debug', 0);
			header("HTTP/1.1 401 Unauthorized");
			echo "Invalid OAuth Request";
			exit;
		}
		return $valid;
	}

/**
 * Configure oauth common settings
 *
 * @return boolean
 */
	public function configureOAuth($consumer = null, $token = null, $options = array()) {
		$this->default = array( 'consumer' => $consumer,
			   'token' => $token,
			   'scheme' => 'header',
			   'signature_method' => null,
			   'nonce' => null,
			   'timestamp' => null);
		$this->options = array_merge($this->default, $options);
    }

/**
 * Signing oauth request
 *
 * @return boolean
 */
	public function applyOAuth() {
		$options = array_merge($this->default, $this->options);
		if ($this->useOauth) {
			return;
		}
		$this->oauthHelper = new ClientHelper($this, array_merge($this->options, array('request_uri' => $this->params['url']['url'])));
		$header = array();
		$this->oauthHelper->amendUserAgentHeader($header);
		if (!empty($header['User-Agent'])) {
			header('User-Agent:' . $header['User-Agent']);
		}

		$method = "__setOAuth" . Inflector::camelize($options['scheme']);
		return $this->{$method}();
    }

/**
 * Header signing auth method implementation
 *
 * @return void
 */
	private function __setOAuthHeader() {
		header('Authorization:' . $this->oauthHelper->header());
	}

/**
 * Configure oauth parameters
 *
 * @return boolean
 */
	function setOAuthParameters() {
		$this->queryParameters = $this->oauthHelper->parametersWithOauth();
		$this->queryParameters = array_merge($this->queryParameters, array('oauth_signature' => $this->oauthHelper->signature()));
		return $this->queryParameters;
    }

/**
 * Not implemented!
 * Possible need to implemented in the special view class
 *
 * @return boolean
 */
	private function __setOAuthBody() {
	}

/**
 * Not possible to rewrite url request in response
 * Will not implemented
 *
 * @return boolean
 */
	private function __setOAuthQueryString() {
	}

/**
 * Oauth request parameters
 *
 * @return boolean
 */
	public function oauthParameters() {
		$proxy = RequestFactory::proxy($this);
		return $proxy->parameters();
	}

/**
 * Build url for redirection
 *
 * @return string
 */
	protected function _gatherUrl($request) {
        return $request->here(false);
	}
	
	
}