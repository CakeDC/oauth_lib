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
 * Parses a URI, provides details and a full correctly composed URI, processes a relative URI redirect
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.uri
 */ 
class URI {

/**
 * Path type REL, FULL
 *
 * @var string
 */
	public $type;

/**
 * URI schema part
 *
 * @var string
 */
	public $scheme;
	
/**
 * Host name
 *
 * @var string
 */
	public $host;
	
/**
 * User (for basic HTTP authentication)
 *
 * @var string
 */
	public $user;
	
/**
 * Password (for basic HTTP authentication)
 *
 * @var string
 */
	public $pass;
	
/**
 * Path part
 *
 * @var string
 */
	public $path;
	
/**
 * Directory part of the path (not part of RFC)
 *
 * @var string
 */
	public $dir;
	
/**
 * Query string
 *
 * @var string
 */
	public $query;
	
/**
 * Port to connect to (if not 80)
 *
 * @var string
 */
	public $port;
	
/**
 * Port part including preceeding semicolon
 *
 * @var string
 */
	public $port_string;
	
/**
 * The anchor (after #)
 *
 * @var string
 */
	public $fragment;
	
/**
 * Full correct URI in the RFC-compliant format
 *
 * @var string
 */
	public $full;
	
/**
 * Flag defines whether to process "/../", "/./", "//" and convert backslash to slashes
 *
 * @var bool
 */
	public $normalize_path = false;
	
/**
 * Symbol used to mark a start of a query
 *
 */
	public $QUERY_DELIMITER = '?';
	
/**
 * regex
 *
 * @param string $name 
 * @return mixed
 */
	static function regex($name) {
		$patternAlpha = "a-zA-Z";
		$patternAlnum = "" . $patternAlpha . "\\d";
		$patternHex = "a-fA-F\\d";
		$patternEscaped = "%[" . $patternHex . "]{2}";
		$patternUnreserved = "-_.!~*'()" . $patternAlnum . "";
		$patternReserved = ";\/?:@&=+$,\\[\\]";
		$Escaped = '/' . $patternEscaped . '/';
		$Unsafe = "/[^" . $patternUnreserved . $patternReserved . "]/";
		if (isset(${$name})) {
			return ${$name};
		}
		return false;
	}
	
/**
 * Constructor
 *
 * @param string $new_uri 
 */
	public function __construct($new_uri = null) {
		if (!empty($new_uri)) {
			$this->process($new_uri);
		}
	}
	
/**
 * Parse the URI, return false on error
 *
 * @param string $new_uri
 * @return bool
 */
	public function process($new_uri) {
		$this->type = 'FULL';
		//init variables, results of parse_url() may redefine them
		$this->scheme = '';
		$this->host = '';
		$this->user = '';
		$this->pass = '';
		$this->path = '';
		$this->dir = '';
		$this->query = '';
		$this->fragment = '';
		$this->full = '';
		$this->port = 80;
		$this->port_string = ':80';
		if (strpos($new_uri, '://') === false) {
			$local_uri = 'http://sample.com' . $new_uri;
			$new_uri = 'http://' . $new_uri;
		}
		$uri_array = @parse_url($new_uri);
		if (!$uri_array) {
			$uri_array = @parse_url($local_uri);
			if (!$uri_array) {
				return false;
			} else {
				$this->type = 'REL';
				$uri_array['host'] = '';
			}
		}
		if (empty($uri_array['scheme'])) {
			$uri_array['scheme'] = 'http://';
		} else {
			$uri_array['scheme'] = $uri_array['scheme'] . '://';
		}
		if (!empty($uri_array['user'])) {
			if (!empty($uri_array['pass'])) {
				$uri_array['pass'] = ':' . $uri_array['pass'] . '@';
			} else {
				$uri_array['user'] .= '@';
				$uri_array['pass'] = '';
			}
		} else {
			$uri_array['user'] = $uri_array['pass'] = '';
		}
		if (!empty($uri_array['port'])) {
			$uri_array['port_string'] = ':' . $uri_array['port'];
		} else {
			$uri_array['port'] = 80;
			$uri_array['port_string'] = '';
		}
		if (empty($uri_array['path']) || !trim($uri_array['path'])) {
			$uri_array['path'] = '/';
		}
		$uri_array['dir'] = $this->dirname($uri_array['path']);
		if (empty($uri_array['query'])) {
			$uri_array['query'] = '';
		} else {
			$uri_array['query'] = '?' . $uri_array['query'];
		}
		if (empty($uri_array['fragment'])) {
			$uri_array['fragment'] = '';
		} else {
			$uri_array['fragment'] = '#' . $uri_array['fragment'];
		}
		foreach($uri_array as $key => $value) {
			$this->$key = $value;
		}
		$this->get_full_uri();
		return true;
	}
	
/**
 * Processes a new URI using details of a previous one
 *
 * @param string $new_url
 * @return bool
 */
	public function parse_http_redirect($new_url) {
		if (!$new_url || !is_string($new_url)) {
			return false;
		}
		if ($method_pos = strpos($new_url, '://')) {
			$method = substr($new_url, 0, $method_pos);
			if (!strcasecmp($method, 'http') || !strcasecmp($method, 'https')) {
				return $this->process($new_url);
			} else {
				return false;
			}
		}
		$param_pos = strpos($new_url, $this->QUERY_DELIMITER);
		if ($param_pos !== false) {
			$new_query = substr($new_url, $param_pos);
			$new_path = $param_pos ? substr($new_url, 0, $param_pos) : '';
		} else {
			$new_path = $new_url;
			$new_query = '';
		}
		if ($new_url[0] != '/') {
			$new_path = $this->dirname($this->path) . '/' . $new_path;
		}
		if ($this->normalize_path) {
			$new_path = preg_replace('~((\\\\+)|/){2,}~', '/', $new_path);
			if (strpos($new_path, '/../') !== false) {
				$path_array = explode('/', $new_path);
				foreach($path_array as $key => $value) {
					if ($value == '..') {
						if ($key>2) {
							unset($path_array[$key-1]);
						}
						unset($path_array[$key]);
					}
				}
				$new_path = implode('/', $path_array);
			}
			$new_path = str_replace('/./', '/', $new_path);
		}
		$this->path = $new_path;
		$this->query = $new_query;
		$this->get_full_uri();
		return true;
	}
	
/**
 * Returns the directory part of the path (path parameter may include query string)
 *
 * @param string $path
 * @return string
 */
	public function dirname($path) {
		if (!$path) {
			return false;
		}
		$i = strpos($path, '?');
		$dir = $i ? substr($path, 0, $i) : $path;
		$i = strrpos($dir, '/');
		$dir = $i ? substr($dir, 0, $i) : '/';
		if (!($dir[0] == '/')) {
			$dir = '/' . $dir;
		}
		return $dir;
	}
	
/**
 * (re)compile the full uri and return the string
 *
 * @return string
 */
	public function get_full_uri() {
		if ($this->type == 'REL') {
			$this->full = $this->localPath();
		}
		if ($this->type == 'FULL') {
			$this->full = $this->fullPath();
		}
		return $this->full;
	}
	
/**
 * to string
 *
 * @return string
 */
	public function toString() {
		return $this->get_full_uri();
	}
	
/**
 * to string
 *
 * @return void
 */
	public function __toString() {
		return $this->get_full_uri();
	}
	
/**
 * full path
 *
 * @return string
 */
	public function fullPath() {
		$this->full = $this->scheme . $this->user . $this->pass . $this->host . $this->port_string . $this->path . $this->queryWithQ();
		return $this->full;
	}
	
/**
 * server path
 *
 * @return string
 */
	public function serverPath() {
		$this->full = $this->scheme . $this->user . $this->pass . $this->host . $this->port_string . $this->path;
		return $this->server_path;
	}
	
/**
 * local path
 *
 * @return string
 */
	public function localPath() {
		if ((substr($this->query, 0, 1) != '?') && strlen($this->query)>0) {
			$this->query = '?' . $this->query;
		}
		$this->local_path = $this->path . $this->query;
		return $this->local_path;
	}
	
/**
 * path
 *
 * @return string
 */
	public function path() {
		if ($this->type == 'REL') {
			return $this->localPath();
		}
		if ($this->type == 'FULL') {
			return $this->serverPath();
		}
		return false;
	}
	
/**
 * Checks if the requested host exists
 *
 * @return bool
 */
	public function checkHost() {
		if (!$this->host) {
			throw new URLException(120);
		}
		$regexp = '/^\d{2,3}(\.\d{1,3}){3}$/';
		if (!checkdnsrr($this->host, 'A') && !preg_match($regexp, $this->host)) {
			throw new URLException(120);
		}
	}
	
/**
 * Unescapes the string.
 *
 * $enc_uri = URI::escape("http://example.com/?a=\11\15");
 * echo $enc_uri;
 * : "http://example.com/?a=%09%0D";
 *
 * echo URI::unescape($enc_uri);
 * echo URI::unescape("http://example.com/?a=%09%0D");
 * : "http://example.com/?a=\t\r"
 */
	public function unescape($str) {
		$escaped = URI::regex('Escaped');
		if (preg_match_all($escaped, $str, $matches) && count($matches) >0) {
			$str = preg_replace_callback($escaped, create_function('$matches', 'return chr(hexdec(substr($matches[0], 1)));'), $str);
		}
		return $str;
	}

/**
 * Escapes the string, replacing all unsafe characters with codes.
 *
 * @param string $str, string to replaces in.
 * @param string $unsafe, Regexp that matches all symbols that must be replaced with codes.
 */
	public function escape($str, $unsafe = null) {
		if (!isset($unsafe)) {
			$unsafe = URI::regex('Unsafe');
		} else {
			$unsafe = "/" . $unsafe . "/";
		}
		if (preg_match_all($unsafe, $str, $matches) && count($matches) >0) {
			$str = preg_replace_callback($unsafe, create_function('$matches', '
				$replace = $matches[0];
				$result = "";
				for($i=0; $i<strlen($replace); $i++) { 
					$result .= sprintf(\'%%%02X\', ord($replace[$i])); 
				}
				return $result;'), $str);
		}
		return $str;
	}

/**
 * Set host
 *
 * @param string $host 
 * @param string $port 
 * @return void
 */
	public function setHost($host, $port) {
		$this->host = $host;
		$this->port = $port;
		$this->type = 'FULL';
	}

/**
 * query
 *
 * @return mixed
 */
	public function query() {
		if (substr($this->query, 0, 1) == '?') {
			return substr($this->query, 1);
		}
		return $this->query;
	}

/**
 * queryWithQ
 *
 * @return string
 */
	public function queryWithQ() {
		if (strlen($this->query)>0) {
			if (substr($this->query, 0, 1) == '?') {
				return $this->query;
			}
			return $this->QUERY_DELIMITER . $this->query;
		} else {
			return "";
		}
	}
}
