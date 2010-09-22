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
 * Mock Object used to store any type of data.
 * 
 * @package oauth_lib
 * @subpackage oauth_lib.libs.request_proxy
 */
class MockObject {

/**
 * s
 *
 * @var string
 */
	protected $s=array();

/**
 * Setter
 *
 * @param string $k 
 * @param string $c 
 * @return void
 */
	function __set($k, $c) { $this->s[$k]=$c; }

/**
 * get
 *
 * @param string $k 
 * @return void
 * @author Predominant
 */
	function __get($k) { return $this->s[$k]; } 

/**
 * Configuaration options
 *
 * @var array $options
 */
	public $data;

/**
 * Constructor
 *
 * @param Object $request
 * @param array $options
 */
	public function __construct($data = array()) {
		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}
		$this->data = $data;
	}
}

/**
 * Request proxy mock class. Provide interface to extract info from MockObject
 * 
 * @package oauth_lib
 * @subpackage oauth_lib.libs.request_proxy
 */
RequestFactory::register('MockObject', 'RequestProxyMock');
class RequestProxyMock extends RequestProxyBase {

/**
 * Request Object
 *
 * @var Object $request
 */
	public $request;

/**
 * Configuaration options
 *
 * @var array $options
 */
	public $options;

/**
 * Get request method
 *
 * @return string
 */
	public function method() {
		return $this->request->method;
	}

/**
 * Get request uri
 *
 * @return string
 */
	public function uri() {
		return $this->request->uri;
	}

/**
 * Get request parameter 
 *
 * @return array
 */
	public function parameters() {
		return $this->request->parameters;
	}
	
	public function setParameters($value) {
		$this->request->parameters = $value;
	}

/**
 * Mock for nomalized uri method
 *
 * @return array
 */
	public function normalizedUri() {
		try {
			return parent::normalizedUri();
		} catch(Exception $e) {
			return $this->uri();
		}
	}
}
