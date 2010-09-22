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
App::import('CakeLog');
App::import('Lib', 'OauthLib.Exceptions');

/**
 * Oauth helper library contain widespread used methods.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */
class OauthHelper {

/**
 *	List of parameters that described by oauth specification
 *
 * @var array
 */
	public static $parameters = array(
		'oauth_callback', 'oauth_consumer_key', 'oauth_token', 'oauth_signature_method',
		'oauth_timestamp', 'oauth_nonce', 'oauth_verifier', 'oauth_version', 'oauth_signature');

/**
 * Large random number generator
 *
 * @param integer $powerOfTwo
 * @return string
 */
	public function random($powerOfTwo) {
		$prefix = 'P';
		if (extension_loaded('bcmath')) {
			OauthHelper::log($prefix . OauthHelper::bcrandom(1, bcpow(2, $powerOfTwo)));
			return $prefix . OauthHelper::bcrandom(1, bcpow(2, $powerOfTwo));
		}
		if (extension_loaded('gmp')) {
			$limbOp = round($powerOfTwo / 32);
			OauthHelper::log($prefix . gmp_strval(gmp_random($limbOp)));
			return $prefix . gmp_strval(gmp_random($limbOp)); 	
		}
		return $prefix . rand(0, pow(2, $powerOfTwo));
    }

/**
 * Generate BC based random number
 *
 * @param integer $min
 * @param integer $max
 * @return string
 */
	function bcrandom($min, $max) {
		bcscale(0);
		if (bccomp($max,$min)!=1) {
			return 0;
		}
		$top = bcsub($max, $min);
		$rand = bcadd($top, 1);
		$length = strlen($top);
	 
		$n = 0;
		while (9 * $n <= $length) {
			if ($length - 9 * $n >= 9) {
				$rand_part[] = rand(0,999999999);
			} else {
				$j = 0; 
				$foo = '';
				while($j < $length - 9 * $n) {
					$foo .= '9';
					++$j;
				}
				$foo += 0;
				$rand_part[] = rand(0, $foo);
			}
			$n++;
		}
		$i = 0;
		$rand ='';
		$count = count($rand_part);
		while($i < $count){
			$rand .= $rand_part[$i];
			$i++;
		}
		while(bccomp($rand, $top)==1){
			$rand = substr($rand, 1, strlen($rand)) . rand(0, 9);
		}
		return bcadd($rand, $min);
	}

/**
 * Map list of parameters to url
 *
 * @param integer $size
 * @return string
 */
	public function mapper($params, $separator, $quote = '"') {
		$paramList = array();
		foreach($params as $k => $v) {
			$paramList[] = urlencode($k) . '=' . $quote.urlencode($v) . $quote;
		}
		return implode($separator, $paramList);
	}

/**
 * Escape list of parameters 
 *
 * @param mixed $value
 * @return string
 */
	public function escape($value) {
		if ($value === false) {
			return $value;
		} else {
			if (is_array($value)) {
				$value = implode('+', $value);
			}
			$value = str_replace('%7E', '~', rawurlencode($value));
			return str_replace('+', '%2B', $value);
		}
	}

/**
 * generate secret key
 *
 * @param integer $size
 * @return string
 */
	public function generateKey($size = 32) {
		$randomBytes = '';
		for ($i = 0;$i<$size;$i++) {
			$randomBytes.= chr(rand(1, 255));
		}
		$code = base64_encode($randomBytes);
		return ereg_replace('[^a-zA-Z0-9]', '', $code);
	}

/**
 * Encode a string according to the RFC3986
 *
 * @param string $s
 * @return string
 */
	public function urlencode($s) {
		if ($s === false) {
			return $s;
		} else {
			return str_replace('%7E', '~', rawurlencode($s));
		}
	}

/**
 * Decode a string according to RFC3986.
 * Also correctly decodes RFC1738 urls.
 *
 * @param string $s
 * @return string
 */
	public function urldecode($s) {
		if ($s === false) {
			return $s;
		} else {
			return rawurldecode($s);
		}
	}

/**
 * urltranscode - make sure that a value is encoded using RFC3986.
 * We use a basic urldecode() function so that any use of '+' as the
 * encoding of the space character is correctly handled.
 *
 * @param string $s
 * @return string
 */
	public function urltranscode($s) {
		if ($s === false) {
			return $s;
		} else {
			return $this->urlencode(urldecode($s));
		}
	}

/**
 * Internal oauth logging method
 *
 * @param mixed $msg, message to log
 * @param string $typ, type of log
 * @return bool log write result
 */	
	public function log($msg, $typ = 'oauth') {
		if (Configure::read('debug') == 0) {
			return true;
		}
		if (!class_exists('CakeLog')) {
			uses('cake_log');
		}
		if (!class_exists('CakeLog')) {
			return;
		}
		$log = new CakeLog();
		if (!is_string($msg)) {
			$msg = print_r($msg, true);
		}
		$backtrace = debug_backtrace();
		$file = $line = $function = $object = '';
		if (isset($backtrace[0])) {
			extract($backtrace[0]);
		}
		if (isset($backtrace[1])) {
			$obj = get_class($backtrace[1]['object']);
			$function = $backtrace[1]['function'];
		}
		return $log->write($typ, env('HTTP_HOST') . ": object($obj) file($file) line($line) function($function) \n" . $msg);
	}

/**
 * Get one of preset regex
 *
 * @param string $name: patternAlpha, patternAlnum, patternHex, patternEscaped, patternUnreserved, patternReserved
 * @return mixed
 */
	public static function regex($name) {
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
 * Unescape
 *
 * @param string $str
 * @return string
 */
	public function unescape($str) {
		$escaped = OauthHelper::regex('Escaped');
		if (preg_match_all($escaped, $str, $matches) && count($matches) > 0) {
			$str = preg_replace_callback($escaped, create_function('$matches', 'return chr(hexdec(substr($matches[0], 1)));'), $str);
		}
		return $str;
	}

/**
 * get base URI by given url
 *
 * @param string $url
 * @return string
 */
	public function getBaseUri($url) {
		if (!class_exists('HttpSocket')) {
			//App::import('Core', 'HttpSocket');
			App::import('Vendor', 'OauthLib.HttpSocket');
		}
		$socket = & new HttpSocket();
		$url = $socket->parseUri($url);
		$url['query'] = '';
		$url['fragment'] = '';
		$url['scheme'] = strtolower($url['scheme']);
		$url['host'] = strtolower($url['host']);
		return $socket->buildUri($url);
	}

/**
 * Normalize  parameter values. Parameters are sorted by name, using lexicographical byte value ordering. 
 * If two or more parameters share the same name, they are sorted by their value.
 * Parameters are concatenated in their sorted order into a single string. 
 * For each parameter, the name is separated from the corresponding value by an "=" character, 
 * even if the value is empty. Each name-value pair is separated by an "&" character.
 */
	public function normalize($params) {
		ksort($params);
		$paramList = array();
		foreach($params as $k => $values) {
			if (is_array($values)) {
				asort($values);
				foreach($values as $v) {
					$paramList[] = OauthHelper::escape($k) . '=' . OauthHelper::escape($v);
				}
			} else {		
				$paramList[] = OauthHelper::escape($k) . '=' . OauthHelper::escape($values);
			}
		}
		return implode('&', $paramList);
	}

/**
 * Parse uri wrapper. Handle both relative and global uri
 *
 * @param string $uri
 * @return array
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
 * Build uri wrapper.
 *
 * @param array $options
 * @return string
 */
	public function buildUri($options) {
		$sock = new HttpSocket;
		return @$sock->buildUri($options);
	}
}
