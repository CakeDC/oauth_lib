<?php

class OauthException extends Exception {
}

class UnauthorizedException extends OauthException {
    public $request = null;
    public function __construct($request = null) {
		$this->request = $request;
		parent::__construct();
    }
	
	public function requestBody() {
		return $this->request['body'];
	}
	
    public function __toString() {
		if (!empty($this->request)) {
			return $this->request['status']['code'] . ' ' . $this->request['body'];
		} else {
			return '';
		}
    }
}

class ProblemException extends UnauthorizedException {
    public $problem = null;
    public $params = null;
    public function __construct($problem, $request = null, $params = array()) {
		parent::__construct($request);
		$this->problem = $problem;
		$this->params = $params;
    }

	public function __toString() {
		return $this->problem;
	}
}


?>