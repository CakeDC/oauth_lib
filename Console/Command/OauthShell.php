<?php 
/**
 * Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010-2012, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
 
App::uses('OauthHelper', 'OauthLib.Lib');
App::uses('Consumer', 'OauthLib.Lib');
App::uses('RequestToken', 'OauthLib.Lib');
App::uses('RequestFactory', 'OauthLib.Lib');

/**
 * Oauth shell allow to perform authorize, sign, query signed data and perform xauth operations.
 * 
 * @package 	oauth_lib
 * @subpackage	oauth_lib.vendors.shells
 */
class OauthShell extends Shell {

/**
 * Internal options
 *
 * @var array
 */
	public $options = array();
	
/**
 * debug messages flags
 *
 * @var boolean
 */
	public $debugMessages = false;
	
/**
 * Override startup
 *
 * @return void
 */
	public function startup() {

		$this->__settings();
		if (!$this->__enoughOptions($this->command)) {
			$this->out('Not enough options:');
			$this->out($this->OptionParser->help($this->command)); 
			exit();
		}
	}	

/**
 * get the option parser.
 *
 * @return void
 */
	public function getOptionParser() {

		$parser = parent::getOptionParser();
		return $parser->description('Supported commands:')
			->addOption('header', array(
				'help' => 'Use the Authorization header for OAuth parameters (default)',
				'boolean' => true,
				'default' => true,
			))
			->addOption('body', array(
				'help' => 'Use the request body for OAuth parameters.',
				'boolean' => true,
			))
			->addOption('query_string', array(
				'help' => 'Use the query string for OAuth parameters.',
				'boolean' => true,
			))
			->addOption('consumer_key', array(
				'help' => 'Specifies the consumer key to use.',
			))
			->addOption('consumer_secret', array(
				'help' => 'Specifies the consumer secret to use.',
			))

			->addOption('method', array(
				'help' => 'Specifies the method (e.g. GET) to use when signing.',
				'required' => false))
			->addOption('nonce', array(
				'help' => 'Specifies the nonce to use.',
				'required' => false))
			->addOption('parameters', array(
				'help' => 'Specifies the parameters to use when signing.',
				'required' => false))
			->addOption('signature-method', array(
				'help' => 'Specifies the signature method to use; defaults to HMAC-SHA1.',
				'required' => false))
			->addOption('secret', array(
				'help' => 'Specifies the token secret to use.',
				'required' => false))
			->addOption('timestamp', array(
				'help' => 'Specifies the timestamp to use.',
				'required' => false))
			->addOption('token', array(
				'help' => 'Specifies the token to use.',
				'required' => false))
			->addOption('realm', array(
				'help' => 'Specifies the realm to use.',
				'required' => false))
			->addOption('uri', array(
				'help' => 'Specifies the uri to use.',
				'required' => false))
			->addOption('version', array(
				'help' => 'Specifies the version to use.',
				'required' => false))
			->addOption('no_version', array(
				'help' => 'Omit oauth_version',
				'boolean' => true,
				'required' => false))
			->addOption('debug', array(
				'help' => 'Be verbose',
				'boolean' => true,
				'required' => false))

				
			->addOption('access_token_url', array(
				'help' => 'Specifies the access token URL.',
				'required' => false))
			->addOption('authorize_url', array(
				'help' => 'Specifies the authorization URL.',
				'required' => false))
			->addOption('callback_url', array(
				'help' => 'Specifies a callback URL.',
				'required' => false))
			->addOption('request_token_url', array(
				'help' => 'Specifies the request token URL.',
				'required' => false))
			->addOption('scope', array(
				'help' => 'Specifies the scope (Google-specific).',
				'required' => false))
			
			
			->addSubcommand('xauth', array(
					'help' => 'Perform xauth-based authorization on server'))
			->addSubcommand('sign', array(
					'help' => 'Generate an OAuth signature'))
			->addSubcommand('debug', array(
					'help' => 'Generate an OAuth signature'))
			->addSubcommand('query', array(
					'help' => 'Do call to oauth protected resource'))
			->addSubcommand('authorize', array(
					'help' => 'Perform authorize request to retrieve access token and secret by user'));
	}
	
/**
 * Parse settings
 *
 * @return void
 */
	private function __settings() {
		$this->options = array(
			'oauth_nonce' => OauthHelper::generateKey(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0',
			'method' => 'POST',
			'params' => array(),
			'scheme' => 'header',
			'version' => '1.0');
		$this->options['parameters'] = array();
		$values = array('body' => 'scheme', 'header' => 'scheme', 'query_string' => 'scheme');
		$map = array(
			'consumer_key' => 'oauth_consumer_key',
			'consumer_secret' => 'oauth_consumer_secret',
			'method' => 'method',
			'nonce' => 'oauth_nonce',
			'signature_method' => 'oauth_signature_method',
			'secret' => 'oauth_token_secret',
			'token' => 'oauth_token',
			'timestamp' => 'oauth_timestamp',
			'realm' => 'realm',
			'uri' => 'uri',
			'version' => 'oauth_version',
			'access_token_url' => 'access_token_url',
			'authorize_url' => 'authorize_url',
			'callback_url' => 'oauth_callback',
			'request_token_url' => 'request_token_url',
			'scope' => 'scope',
			'username' => 'username',
			'password' => 'password',
		);

		foreach ($values as $k => $v) {		
			if (!empty($this->params[$k])) {
				$this->options[$v] = $k;
			}
		}
		foreach ($map as $param => $key) {		
			if (!empty($this->params[$param])) {
				$this->options[$key] = $this->params[$param];
			}
		}
		if (!empty($this->params['options'])) {	
			// @todo
		}

		if (!empty($this->params['parameters'])) {	
			$this->options['parameters'] = split(',', $this->params['parameters']);
		}
		if (!empty($this->params['no_version'])) {	
			$this->options['version'] = null;
		}
		if (!empty($this->params['debug'])) {	
			$this->options['debug'] = true;
			$this->debugMessages = true;
		}
	}
	
/**
 * Check possibility to perform operation
 *
 * @param string $command
 * @return boolean
 */
   private function __enoughOptions($command) {
		if ($command == 'authorize') {
			return isset($this->options['oauth_consumer_key']) && isset($this->options['oauth_consumer_secret'])
			&& isset($this->options['access_token_url']) && isset($this->options['authorize_url']) 
			&& isset($this->options['request_token_url']);
		} elseif (in_array($command, array('query', 'sign', 'debug'))) {
			return isset($this->options['oauth_consumer_key']) && isset($this->options['oauth_consumer_secret'])
			&& isset($this->options['method']) && isset($this->options['uri']);
		}
		return true;
    }
	
/**
 * Perform authorize operation
 *
 */
	public function authorize() {
		$options = array(
			'uri' => $this->options['uri'],
			'access_token_uri' => $this->options['access_token_url'],
			'authorize_uri' => $this->options['authorize_url'],
			'request_token_uri' => $this->options['request_token_url'],
			'scheme' => $this->options['scheme'],
			'http_method' => $this->options['method']
		);
		$Consumer = new Consumer($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $options);
		$Consumer->http = new HttpSocket($this->options['uri']);
		
		// parameters for OAuth 1.0a
		$oauthVerifier = null;
		
		$Consumer->init($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $options);
		try {
			$RequestToken = $Consumer->getRequestToken(array('oauth_callback' => $this->options['oauth_callback']), array('scope' => @$this->options['scope']));
			if ($RequestToken->isCallbackConfirmed()) {
				$this->out('Server appears to support OAuth 1.0a; enabling support.');
				$this->options['version'] = '1.0a';
			}
			$this->out('Please visit this url to authorize: ' . $Consumer->authorizeUrl());

			if ($this->options['version'] == '1.0a') {
			  $oauthVerifier = $this->in('Please enter the verification code provided by the SP (oauth_verifier):');
			} else {
			  $this->in('Press return to continue...');
			}
			
			try {
				$AccessToken = $RequestToken->getAccessToken(array('oauth_verifier' => $oauthVerifier));
				$this->out('Response: ');
				// foreach ($AccessToken->tokenParams as $k => $v) {
					// $this->out($k . ' : ' . $v);
				// }
				$this->out('token: ' . $AccessToken->token);
				$this->out('token secret: ' . $AccessToken->tokenSecret);
			} catch (UnauthorizedException $e) {
	              $this->out('A problem occurred while attempting to obtain an access token:');
	              $this->out($e->getMessage());
	              $this->out($e->requestBody());
			} catch (Exception $e) {
				$this->out($e->getMessage());
			}
		} catch (UnauthorizedException $e) {
			  $this->out('A problem occurred while attempting to authorize:');
			  $this->out($e->getMessage());
			  $this->out($e->requestBody());
		} catch (Exception $e) {
			$this->out($e->getMessage());
		}
	}
		
/**
 * Perform xauth operation
 *
 */
	public function xauth() {
		$options = array(
			'uri' => $this->options['uri'],
			'username' => $this->options['username'],
			'password' => $this->options['password'],
			'access_token_uri' => $this->options['access_token_url'],
			'scheme' => $this->options['scheme'],
			'http_method' => $this->options['method']
		);
		$Consumer = new Consumer($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $options);
		$Consumer->http = new HttpSocket($this->options['uri']);
		
		// parameters for OAuth 1.0a
		$oauthVerifier = null;		
		$Consumer->init($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $options);
		try {
			
			try {
				$AccessToken = $Consumer->getAccessToken(null, array(), array('x_auth_mode' => 'client_auth', 'x_auth_username' => $this->options['username'], 'x_auth_password' => $this->options['password']));
				$this->out('Response: ');
				// foreach ($AccessToken->tokenParams as $k => $v) {
					// $this->out($k . ' : ' . $v);
				// }
				$this->out('token: ' . $AccessToken->token);
				$this->out('token secret: ' . $AccessToken->tokenSecret);
			} catch (UnauthorizedException $e) {
	              $this->out('A problem occurred while attempting to obtain an access token:');
	              $this->out($e->getMessage());
	              $this->out($e->requestBody());
			} catch (Exception $e) {
				$this->out($e->getMessage());
			}
		} catch (UnauthorizedException $e) {
			  $this->out('A problem occurred while attempting to authorize:');
			  $this->out($e->getMessage());
			  $this->out($e->requestBody());
		} catch (Exception $e) {
			$this->out($e->getMessage());
		}
	}
	
	public function debug() {
		$this->options['debug'] = true;
		$this->debugMessages = true;
        $this->sign();
	}
	
/**
 * Perform sign operation
 *
 */
	public function sign() {
		$this->command = 'sign';
		if (!$this->__enoughOptions($this->command)) {
			return false;
		}
		$parameters = $this->__prepareParams();
		  
		$Request = RequestFactory::proxy(new MockObject(array('parameters' => $parameters, 'method' => $this->options['method'], 'uri' => $this->options['uri'])));
		
		if ($this->debugMessages) {
			$this->out('OAuth parameters:');
			foreach ($Request->oauthParameters() as $name => $value) {
				$this->out("  $name: $value", false);
			}
			$this->out();
			$otherParams = $Request->nonOauthParameters();
			if (!empty($otherParams)) {
				$this->out('Parameters:');
				foreach ($Request->nonOauthParameters() as $name => $value) {
					$this->out("  $name: $value", false);
				}
				$this->out();
			}
		} 
		
		$Request->sign(array('consumer_secret' => $this->options['oauth_consumer_secret'], 'token_secret' => $this->options['oauth_token_secret']));	

		if ($this->debugMessages) {
			$this->out('Method: ' . $Request->method());
			$this->out('URI: ' . $Request->uri());
			$this->out('Normalized params: ' . $Request->normalizedParameters());
			$this->out('Signature base string: ' . $Request->signatureBaseString());

			$this->out('OAuth Request URI: ' . $Request->signedUri());
			$this->out('Request URI: ' . $Request->signedUri(false));
			if (empty($this->options['realm'])) {
				$this->options['realm'] = '';
			}
			$this->out('Authorization header: ' . $Request->oauthHeader(array($this->options['realm'])));

            $this->out('Signature: ' . $Request->signature());
            $this->out('Escaped signature: ' . OauthHelper::escape($Request->signature()));
		} else {
            $this->out($Request->signature());
		}
	}

/**
 * Perform query operation
 *
 */
	public function query() {
		$this->command = 'query';
		if (!$this->__enoughOptions($this->command)) {
			return $this->help();
		}
		$options = array(
			'uri' => $this->options['uri'],
			'scheme' => $this->options['scheme'],
			'http_method' => $this->options['method']
		);
		$Consumer = new Consumer($this->options['oauth_consumer_key'], $this->options['oauth_consumer_secret'], $options);
		$Consumer->http = new HttpSocket($this->options['uri']);
		
		$params = $this->__joinParams($this->__prepareParams(false));

		if (!class_exists('HttpSocket')) {
			App::uses('HttpSocket', 'Network/Http');
			App::uses('HttpSocketProxy', 'OauthLib.Lib/Network/Http');
		}
		$socket =  new HttpSocket();
		$proxy = new HttpSocketProxy($socket);
		$uri = $proxy->parseUri($this->options['uri']);
		if (!empty($uri['query'])) {
			$params = array_merge($params, $this->__joinParams($uri['query']));
		}
		if (!empty($params)) {
			$uri['query'] = join('&', $params);
		}
		$uri = $proxy->buildUri($uri);
		$AccessToken = new AccessToken($Consumer, $this->options['oauth_token'], $this->options['oauth_token_secret']);
		if ($AccessToken) {
          $response = $AccessToken->request(strtoupper($this->options['method']), $uri);
		  $this->out($response['status']['code'] . ' ');
		  $this->out($response['body']);
		}
		
	}

/**
 * join params helper method
 *
 * @param array $data
 * @return string
 */
	private function __joinParams($data) {
		$params = array();
		foreach ($data as $key => $values) {
			if (is_array($values)) {
				foreach ($values as $v) {
					$params[] = rawurlencode($key) . '=' . rawurlencode($v);
				}
			} else {
				$params[] = rawurlencode($key) . '=' . rawurlencode($values);
			}
		}
		return $params;
	}
	
/**
 * prepare oauth params helper method
 *
 * @param boolean $includeOauthParams
 * @return string
 */
	private function __prepareParams($includeOauthParams = true) {
		$escapedPairs = array();
		foreach ($this->options['parameters'] as $pair) {
			if (strpos($pair, ':')) {
				list($k, $v) = split(':', $pair);
				$escapedPairs[] = OauthHelper::escape($k) . '=' . OauthHelper::escape($v);
			} else {
				$escapedPairs[] = $pair;
			}
		}
		$queryString = join('&', $escapedPairs);
		parse_str($queryString, $cliParams);
		$keys = array(
	        'oauth_consumer_key', 'oauth_nonce', 'oauth_timestamp', 'oauth_token', 'oauth_signature_method', 'oauth_version');
		$options = array();
		if ($includeOauthParams) {
			foreach ($keys as $key) {
				if (!empty($this->options[$key])) {
					$options[$key] = $this->options[$key];
				}
			}
		}
		return array_merge($options, $cliParams);
	}
}
