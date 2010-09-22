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
 * Implementation of the Hashed Message Authentication Code
 *
 * It provides set of methods to use in combine with Cakephp Auth component to authenticate users
 * with remote auth servers like twitter.com, so users will have transparent authentication later.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.libs
 */
class Hmac extends Object {

/**
 * The key to use for the hash
 *
 * @var string
 */
	protected $_key = null;

/**
 * pack() format to be used for current hashing method
 *
 * @var string
 */
	protected $_packFormat = null;

/**
 * Hashing algorithm; can be the md5/sha1 functions or any algorithm name
 * listed in the output of PHP 5.1.2+ hash_algos().
 *
 * @var string
 */
	protected $_hashAlgorithm = 'md5';

/**
 * Supported direct hashing functions in PHP
 *
 * @var array
 */
	protected $_supportedHashNativeFunctions = array('md5', 'sha1');

/**
 * List of hash pack formats for each hashing algorithm supported.
 * Only required when hash or mhash are not available, and we are
 * using either md5() or sha1().
 *
 * @var array
 */
	protected $_hashPackFormats = array('md5'=>'H32', 'sha1'=>'H40');

/**
 * List of algorithms supported my mhash()
 *
 * @var array
 */
	protected $_supportedMhashAlgorithms = array(
		'adler32', ' crc32', 'crc32b', 'gost', 'haval128', 'haval160', 'haval192', 'haval256',
		'md4', 'md5', 'ripemd160', 'sha1', 'sha256', 'tiger', 'tiger128', 'tiger160');

/**
 * Constants representing the output mode of the hash algorithm
 */
	const STRING = 'string';
	const BINARY = 'binary';

/**
 * Constructor; optionally set Key and Hash at this point
 *
 * @param string $key
 * @param string $hash
 * @return
 */
	public function __construct($key = null, $hash = null) {
		if (!is_null($key)) {
			$this->setKey($key);
		}
		if (!is_null($hash)) {
			$this->setHashAlgorithm($hash);
		}
	}

/**
 * Set the key to use when hashing
 *
 * @param string $key
 * @return OAuthHMAC
 */
	public function setKey($key) {
		if (!isset($key) || empty($key)) {
			throw new Exception('OAuth HMAC: provided key is null or empty');
		}
		$this->_key = $key;
		return $this;
	}

/**
 * Getter to return the currently set key
 *
 * @return string
 */
	public function getKey() {
		if (is_null($this->_key)) {
			throw new Exception('OAuth HMAC: key has not yet been set');
		}
		return $this->_key;
	}

/**
 * Setter for the hash method. Supports md5() and sha1() functions, and if
 * available the hashing algorithms supported by the hash() PHP5 function or
 * the mhash extension.
 *
 * Since they are so many varied HMAC methods in PHP these days this method
 * does a lot of checking to figure out what's available and not.
 *
 * @param string $hash
 * @return OAuthHMAC
 */
	public function setHashAlgorithm($hash) {
		if (!isset($hash) || empty($hash)) {
			throw new Exception('OAuth HMAC: provided hash string is null or empty');
		}
		$hash = strtolower($hash);
		$hashSupported = false;
		if (function_exists('hash_algos') && in_array($hash, hash_algos())) {
			$hashSupported = true;
		}
		if ($hashSupported === false && function_exists('mhash') && in_array($hash, $this->_supportedMhashAlgorithms)) {
			$hashSupported = true;
		}
		if ($hashSupported === false && in_array($hash, $this->_supportedHashNativeFunctions) && in_array($hash, array_keys($this->_hashPackFormats))) {
			$this->_packFormat = $this->_hashPackFormats[$hash];
			$hashSupported = true;
		}
		if ($hashSupported === false) {
			throw new Exception('OAuth HMAC: hash algorithm provided is not supported on this PHP instance; please enable the hash or mhash extensions');
		}
		$this->_hashAlgorithm = $hash;
		return $this;
	}

/**
 * Return the current hashing algorithm
 *
 * @return string
 */
	public function getHashAlgorithm() {
		return $this->_hashAlgorithm;
	}

/**
 * Perform HMAC and return the keyed data
 *
 * @param string $data
 * @param bool $internal Option to not use hash() functions for testing
 * @return string
 */
	public function hash($data, $output = self::STRING, $internal = false) {
		if (function_exists('hash_hmac') && $internal === false) {
			if ($output == self::BINARY) {
				return hash_hmac($this->getHashAlgorithm(), $data, $this->getKey(), 1);
			}
			return hash_hmac($this->getHashAlgorithm(), $data, $this->getKey());
		}
		if (function_exists('mhash') && $internal === false) {
			if ($output == self::BINARY) {
				return mhash($this->_getMhashDefinition($this->getHashAlgorithm()), $data, $this->getKey());
			}
			$bin = mhash($this->_getMhashDefinition($this->getHashAlgorithm()), $data, $this->getKey());
			return bin2hex($bin);
		}
		$key = $this->getKey();
		$hash = $this->getHashAlgorithm();
		if (strlen($key) <64) {
			$key = str_pad($key, 64, chr(0));
		} elseif (strlen($key) >64) {
			$key = pack($this->_packFormat, $this->_digest($hash, $key, $output));
		}
		$padInner = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
		$padOuter = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));
		return $this->_digest($hash, $padOuter . pack($this->_packFormat, $this->_digest($hash, $padInner . $data, $output)), $output);
	}

/**
 * Method of working around the inability to use mhash constants; this
 * will locate the Constant value of any support Hashing algorithm named
 * in the string parameter.
 *
 * @param string $hashAlgorithm
 * @return integer
 */
	protected function _getMhashDefinition($hashAlgorithm) {
		for ($i = 0;$i <= mhash_count();$i++) {
			$types[mhash_get_hash_name($i) ] = $i;
		}
		return $types[strtoupper($hashAlgorithm) ];
	}

/**
 * Digest method when using native functions which allows the selection
 * of raw binary output.
 *
 * @param string $hash
 * @param string $key
 * @param string $mode
 * @return string
 */
	protected function _digest($hash, $key, $mode) {
		if ($mode == self::BINARY) {
			return $hash($key, true);
		}
		return $hash($key);
	}
}
