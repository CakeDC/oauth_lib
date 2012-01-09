<?php

class MockObject {
	protected $s=array();
	function __set($k, $c) { $this->s[$k]=$c; }
	function __get($k) { return $this->s[$k]; } 
/**
 * Configuaration options
 *
 * @var array $options
 * @access public
 */
	public $data;
/**
 * Constructor
 *
 * @param Object $request
 * @param array $options
 * @access public
 */
	public function __construct($data = array()) {
		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}
		$this->data = $data;
	}
}

RequestFactory::register('MockObject', 'RequestProxyMock');


class RequestProxyMock extends RequestProxyBase {
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
 * Constructor
 *
 * @param Object $request
 * @param array $options
 * @access public
 */
	public function __construct(&$request, $options = array()) {
		parent::__construct($request, $options);
	}

/**
 * Get request method
 *
 * @return string
 * @access public
 */
	public function method() {
		return $this->request->method;
	}

/**
 * Get request uri
 *
 * @return string
 * @access public
 */
	public function uri() {
		return $this->request->uri;
	}

/**
 * Get request parameter 
 *
 * @return array
 * @access public
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
 * @access public
 */
	public function normalizedUri() {
		try {
			return parent::normalizedUri();
		} catch(Exception $e) {
			return $this->uri();
		}
	}

}
?>