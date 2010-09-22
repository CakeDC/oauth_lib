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

SimpleTest::ignore('OauthTestCase');

/**
 * Oauth Test case.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests
 */
class OauthTestCase extends CakeTestCase {

/**
 * Map request parameters to string 
 *
 * @return string
 */
	public function requestParametersToS() {
		$paramList = array();
		foreach ($this->requestParameters as $k  =>  $v) {
			$paramList[] = "$k=$v";
		}
		return implode("&", $paramList);
	}

/**
 * Order string splted with & by param names
 *
 * @param string $data 
 * @return string
 */
	public function sorting($data) {
		$arr = explode('&', $data);
		sort($arr);
		return implode('&', $arr);
	}

/**
 * Order string splted with & by param names into array
 *
 * @param string $string 
 * @return array
 */
	public function toOrderedArray($string) {
		$arr = preg_split('/ |&|, /', $string);
		sort($arr);
		return $arr;
	}

/**
 * Return mocked request object
 *
 * @param string $params 
 * @param string $method 
 * @param string $uri 
 * @return Request Object
 */
	public function request($params = array(), $method = 'GET', $uri = "http://photos.example.net/photos") {
		App::import('Lib', 'OauthLib.RequestFactory');
		return RequestFactory::proxy(new MockObject(array('parameters' => $params, 'method' => $method, 'uri' => $uri)));
	}
}
