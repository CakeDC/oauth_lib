<?php

class RequestProxyBase {
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
 *  Additional configuration parameters
 *
 * @var array $parameters
 * @access public
 */
	public $parameters;

/**
 * Constructor
 *
 * @param Object $request
 * @param array $options
 * @access public
 */
	public function __construct(&$request, $options = array()) {
		$this->request = $request;
		$this->options = $options;
	}

/**
 * Register proxy class in factory
 *
 * @param $class name of class to proxy
 * @access public
 */
	public function proxies($class) {
		OAuthRequestFactory::register($class, get_class($this));
	}

/**
 * Get request parameter 
 *
 * @return array
 * @access public
 */
	public function parameters() {
	}

/**
 * Get token
 * 
 * @return string
 * @access public
 */
	public function token() {
		$params = $this->parameters();
		return $params['oauth_token'];
	}
/**
 * Get callback
 *
 * @return string
 * @access public
 */
	public function callback() {
		$params = $this->parameters();
		return $params['oauth_callback'];
	}

/**
 * Get consumer key
 *
 * @return string
 * @access public
 */
	public function consumerKey() {
		$params = $this->parameters();
		return $params['oauth_consumer_key'];
	}

/**
 * Return list of parameters used in signature building
 *
 * @return array
 * @access public
 */
	public function parametersForSignature() {
		$params = $this->parameters();
		unset($params['oauth_signature']);
		return $params;
	}
	
/**
 * Oauth parameters
 *
 * @return array
 * @access public
 */
    public function oauthParameters() {
		$result = array();
		foreach ($this->parameters() as $name => $value) {
			if (in_array($name, OauthHelper::$parameters) && $value != '') {
				$result[$name] = $value;
			}
		}
		return $result;
	}

/**
 * Non oauth parameters
 *
 * @return array
 * @access public
 */
    public function nonOauthParameters() {
		$result = array();
		foreach ($this->parameters() as $name => $value) {
			if (!in_array($name, OauthHelper::$parameters)) {
				$result[$name] = $value;
			}
		}
		return $result;
	}
	
/**
 * Get nonce
 *
 * @return string
 * @access public
 */
	public function nonce() {
		$params = $this->parameters();
		return $params['oauth_nonce'];
	}

/**
 * Get timestamp
 *
 * @return string
 * @access public
 */
	public function timestamp() {
		$params = $this->parameters();
		return $params['oauth_timestamp'];
	}

/**
 * Get verifier
 *
 * @return string
 * @access public
 */
	public function verifier() {
		$params = $this->parameters();
		return $params['oauth_verifier'];
	}

/**
 * Get signature method
 *
 * @return string
 * @access public
 */
	public function signatureMethod() {
		$params = $this->parameters();
		if (!isset($params['oauth_signature_method'])) {
			return null;
		}
		if (is_array($params['oauth_signature_method'])) {
			return $params['oauth_signature_method'][0];
		} else {
			return $params['oauth_signature_method'];
		}
	}

/**
 * Get signature
 *
 * @return string
 * @access public
 */
	public function signature() {
		$params = $this->parameters();
		if (isset($params['oauth_signature'])) {
			if (is_array($params['oauth_signature'])) {
				return array_shift($params['oauth_signature']);
			}
			return $params['oauth_signature'];
		} else {
			return '';
		}
	}

/**
 * Return XAuth parameters fetched from header
 *
 * @return string
 * @access public
 */
    public function xAuthParams() {
		$headers = array('X_AUTH_MODE' => 'x_auth_mode', 'HTTP_X_AUTH_MODE' => 'x_auth_mode', 'X_AUTH_USERNAME' => 'x_auth_username', 'HTTP_X_AUTH_USERNAME' => 'x_auth_username', 'X_AUTH_PASSWORD' => 'x_auth_password', 'HTTP_X_AUTH_PASSWORD' => 'x_auth_password');
		$results = array();
		foreach ($headers as $header => $key) {
			if (empty($results[$key])) {
				$header = env($header);
				if (!empty($header)) {
					$results[$key] = $header;
				}
			}
		}
		return $results;
	}

/**
 * Return parameters fetched from header
 *
 * @return string
 * @access public
 */
    public function headerParams() {
		$headers = array('X-HTTP_AUTHORIZATION', 'Authorization', 'HTTP_AUTHORIZATION', 'HTTP_HTTP_AUTHORIZATION', 'HTTP_X-HTTP_AUTHORIZATION');
		foreach ($headers as $header) {
			$header = env($header);
			if (!$header) {
				continue;
			}
			if (substr($header, 0, 6) != 'OAuth ') {
				continue;
			}
			$header = substr($header, 6, strlen($header));
			$oauthParams = array();
			$oauthParamString = preg_split('/[,]/', $header);
			foreach ($oauthParamString as &$str) {
				list($key, $value) = preg_split('/[=]/', $str);
				$key = $this->unescape(trim($key));
				$value = $this->unescape(trim($value));
				$len = strlen($value);
				if ((substr($value, 0, 1) == "\"") && (substr($value, $len-1, 1) == "\"")) {
					$value = substr($value, 1, $len - 2);
				}
				if (substr($key, 0, 6) == 'oauth_') {
					$oauthParams[$key] = $value;
				}
			}
			ksort($oauthParams);
	        return $oauthParams;
		}
      return array();
    }

/**
 * Unescape wrapper
 *
 * @param string $value
 * @return string
 * @access public
 */
	public function unescape($value) {
		return OauthHelper::unescape(r('+', '%2B', $value));
	}

/**
 * Get public certificate
 *
 * @return string
 * @access public
 */
	public function getPublicCertificate() {		
		return $this->request->getPublicCertificate();
	}

/**
 * Get private certificate
 *
 * @return string
 * @access public
 */
	public function getPrivateCertificate() {		
		return $this->request->getPrivateCertificate();
	}
	
/**
 * By 9.1 in specs
 *
 * @return string
 * @access public
 */
    public function signatureBaseString() {
      $strings = array($this->method(), $this->normalizedUri(), $this->normalizedParameters());
	  $rusult = array();
	  foreach ($strings as $value) {
		$rusult[] = OauthHelper::escape($value);
	  }
      return join('&', $rusult);
    }
    
/**
 * By 9.1.2 in specs
 *
 * @return string
 * @access public
 */
	public function normalizedUri() {
		return OauthHelper::getBaseUri($this->uri());
    }

/**
 * By 9.1.1. in specs Normalize Request Parameters
 *
 * @return string
 * @access public
 */
	public function normalizedParameters() {
		return OauthHelper::normalize($this->parametersForSignature());
	}

/**
 * Sign request using proxy object
 *
 * @return string
 * @access public
 */
	public function sign($options = array()) {
		$params = $this->parameters();
		$params['oauth_signature'] = Signature::sign($this, $options);
		$this->setParameters($params);
		$this->signed = true;
		return $this->signature();
	}

/**
 * Base method for set parameters
 */
	public function setParameters($value) {
	}
	
/**
 * URI, including OAuth parameters
 *
 * @return string
 * @access public
 */
	public function signedUri($withOauth = true) {
		if (!empty($this->signed)) {
			if ($withOauth) {
				$params = $this->parameters();
			} else {
				$params = $this->nonOauthParameters();
			}
			return join('?', array($this->uri(), OauthHelper::normalize($params)));
		} else {
			return "not signed";
		}
	}

/**
 * Authorization header for OAuth
 *
 * @return string
 * @access public
 */
    public function oauthHeader($options = array()) {
		$headerParams = array();
		foreach ($this->oauthParameters() as $name => $value) {
			$headerParams[] = $name . '="' . OauthHelper::escape($value) . '"';
		}
		$headerParamsStr = join(', ', $headerParams);
		if (!empty($this->options['realm'])) {
			$realm = 'realm="' . $this->options['realm'] . '", "';
		} else {
			$realm = '';
		}
		return "OAuth " . $realm . $headerParamsStr;
    }
	
}
?>