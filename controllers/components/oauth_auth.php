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

if (!class_exists('Signature')) {
	App::import('Lib', 'OauthLib.Signature');
}if (!class_exists('RequestProxyController')) {
	App::import('Lib', 'OauthLib.RequestProxyController');
}
if (!class_exists('OauthHelper')) {
	App::import('Lib', 'OauthLib.OauthHelper');
}
if (!class_exists('RequestFactory')) {
	App::import('Lib', 'OauthLib.RequestFactory');
}
if (!class_exists('ClientHttp')) {
	App::import('Lib', 'OauthLib.ClientHttp');
}

/**
 * CakePHP Oauth library oauth auth component. 
 * 
 * It provides set of methods to use in combine with Cakephp Auth component to authenticate users
 * with remote auth servers like twitter.com, so users will have transparent authentication later.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.controllers.components
 */
class OauthAuthComponent extends Object {

/**
 * Controller instance
 *
 * @var AppController
 */
	public $Controller;

/**
 * Flag that identify oauth signed request actions
 *
 * @var string $useOauth
 */
	public $useOauth = false;

/**
 * Parameters show which action need to check with verifyOauthSignature
 *
 * @var array $requireOAuth
 */
	public $requireOAuth = array(
		'actions' => array(),
		'enabled' => false,
		'exit' => false);

/**
 * tokenData, is ServerToken for the request after verifyOauthSignature
 *
 * @var string $tokenData
 */
	public $tokenData = null;

/**
 * Component flag that show if oauth authentication passed. This flag allow to implement parallel access 
 * using Auth and Oauth autentification to same method
 *
 * @var boolean
 */
	public $allowed = true;
	
/**
 * Before filter callback
 * Load Server models and verify oauth request
 *
 * @return boolean
 */
	public function initialize($Controller) {
		$this->allowed = false;
		$this->Controller = $Controller;
		$config = $this->requireOAuth;
		if (!empty($this->Controller->requireOAuth)) {
			$config = $this->Controller->requireOAuth;
		} else {
			$config = $this->requireOAuth;
		}
		$this->config = $config;
		if ($config['enabled']) {
			$this->Controller->_loadOauthModels();
			$actions = $config['actions'];
			if (is_array($actions) && (in_array($this->Controller->action, $actions) || in_array('*', $actions)) || $actions == '*') {
				$result = $this->verifyOauthRequest($config);
				$this->allowed = !empty($result);
			}
			if ($this->allowed) {
				$this->configureOAuth();
				$this->Controller->_afterOauthChecked();
			}
		} else {
			$this->allowed = true;
		}
	}
	
/**
 * Do verify for oauth request
 *
 * @params array $config
 * @return boolean
 */
	public function verifyOauthRequest($config) {
		return $this->verifyOauthSignature($config);
	}

/**
 * Check oauth request signature
 *
 * @params array $config
 * @return boolean
 */
	public function verifyOauthSignature($config) {
		$proxy = & new RequestProxyController($this->Controller);
		$params = $proxy->parameters();
		$token = '';
		if (isset($params['oauth_token'])) {
			$token = $params['oauth_token'];
		}
		$serverRegistry = & new ServerRegistry;
		$this->tokenData = $serverRegistry->AccessServerToken->find(array('AccessServerToken.token' => $token, 'AccessServerToken.authorized' => 1));
		try {
			$valid = Signature::verify($this->Controller, array('consumer_secret' => $this->tokenData['ServerRegistry']['consumer_secret'], 'token_secret' => $this->tokenData['AccessServerToken']['token_secret']));
		} catch(Exception $e) {
			$valid = false;
		}
		if (!empty($config['exit']) && !$valid) {
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
		$this->oauthHelper = new ClientHelper($this->Controller, array_merge($this->options, array('request_uri' => $this->Controller->params['url']['url'])));
		$header = array();
		$this->oauthHelper->amendUserAgentHeader($header);
		if (!empty($header['User-Agent'])) {
			header('User-Agent:' . $header['User-Agent']);
		}

		$method = "__setOAuth" . Inflector::camelize($options['scheme']);
		return $this->{$method}();
    }


/**
 * Before render callback
 *
 * @return boolean
 */
	public function beforeRender() {
		if ($this->allowed && $this->config['enabled']) {
			//$this->applyOAuth();
		}
		//return parent::beforeRender();
	}
	
/**
 * Header signing auth method implementation
 *
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
		$proxy = RequestFactory::proxy($this->Controller);
		return $proxy->parameters();
	}

/**
 * Build url for redirection
 *
 * @return string
 */
	protected function _gatherUrl() {
		$params = $this->Controller->params['url'];
		$url = $params['url'];
		unset($params['url']);
		if (count($params) > 0) {
			$url .= '?' . OauthHelper::mapper($params, '&', '');
		}
		if (strlen($url) > 0 && strpos($url, 0, 1) != '/') {
			$url = '/' . $url;
		}
		if (strlen($url) == 0) {
			$url = '/';
		}
		return $url;
	}
}
