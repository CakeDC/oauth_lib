<?php
SimpleTest::ignore('OauthTestCase');

/**
 * Oauth Test case. Contains comment 
 *
 * @package oauth_lib.tests
 */
class OauthTestCase extends CakeTestCase {

	public function requestParametersToS() {
		$paramList = array();
		foreach ($this->requestParameters as $k  =>  $v) {
			$paramList[] = "$k=$v";
		}
		return implode("&", $paramList);
	}

	public function sorting($data) {
		$arr = explode('&', $data);
		sort($arr);
		return implode('&', $arr);
	}

	public function toOrderedArray($string) {
		// $arr = $string;
		$arr = preg_split('/ |&|, /', $string);
		sort($arr);
		return $arr;
	}

	public function request($params = array(), $method = 'GET', $uri = "http://photos.example.net/photos") {
		App::import('Lib', 'OauthLib.RequestFactory');
		return RequestFactory::proxy(new MockObject(array('parameters' => $params, 'method' => $method, 'uri' => $uri)));
	}

}
?>