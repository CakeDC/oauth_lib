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

App::uses('HttpSocket', 'Network/Http');

/**
 * CakePHP Oauth library for HttpSocket proxy.
 *
 * It provides set of methods hidden in HttpSocket class
 */
class HttpSocketProxy extends HttpSocket {


/**
 * Configuration settings for the HttpSocket and the requests
 *
 * @var array
 */
	public $config = array();
	
/**
 * Confguration settings for the HttpSocket and the requests
 *
 * @var HttpSocket
 */
	public $Socket;

/**
 * Initialize proxy class for HttpSocket to provide access to protected methods of HttpSocket
 *
 * @param HttpSocket $socket
 */
    public function __construct(HttpSocket $socket) {
		$this->Socket = $socket;
		if (!empty($socket)) {
			$this->config = $this->Socket->config;
		}
	}

/**
 * Parses and sets the specified URI into current request configuration.
 *
 * @param string|array $uri URI, See HttpSocket::_parseUri()
 * @return boolean If uri has merged in config
 */
	public function configUri($uri = null) {
		$this->config = $this->Socket->config;
        if ($this->_configUri($uri)) {
            $this->Socket->config = $this->config;
		}
	}

/**
 * Takes a $uri array and turns it into a fully qualified URL string
 *
 * @param string|array $uri Either A $uri array, or a request string. Will use $this->config if left mpty.
 * @param string $uriTemplate The Uri template/format to use.
 * @return mixed A fully qualified URL formatted according to $uriTemplate, or false on failure
 */
	public function buildUri($uri = array(), $uriTemplate = '%scheme://%user:%pass@%host:%port/%path?%query#%fragment') {
		return $this->_buildUri($uri, $uriTemplate);
	}

/**
 * Parses the given URI and breaks it down into pieces as an indexed array with elements
 * such as 'scheme', 'port', 'query'.
 *
 * @param string|array $uri URI to parse
 * @param boolean|array $base If true use default URI config, otherwise indexed array to set 'scheme', 'host', 'port', etc.
 * @return array Parsed URI
 */
	public function parseUri($uri = null, $base = array()) {
		return $this->_parseUri($uri, $base);
	}

/**
 * This function can be thought of as a reverse to PHP5's http_build_query(). It takes a given query string and turns it into an array and
 * supports nesting by using the php bracket syntax. So this means you can parse queries like:
 *
 * - ?key[subKey]=value
 * - ?key[]=value1&key[]=value2
 *
 * A leading '?' mark in $query is optional and does not effect the outcome of this function.
 * For the complete capabilities of this implementation take a look at
 * HttpSocketTest::testparseQuery()
 *
 * @param string|array $query A query string to parse into an array or an array to return
 * directly "as is"
 * @return array The $query parsed into a possibly multi-level array. If an empty $query is
 *     given, an empty array is returned.
 */
	public function parseQuery($query) {
		return $this->_parseQuery($query);
	}
	

}
