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

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

/**
 *  Testing on Construct Request URL
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.spec
 */
class ConstructRequestUrlTest extends OauthTestCase {

/**
 * test1
 *
 * @return void
 */
	public function test1() {
		$this->assertRequestUrl('http://example.com/resource', 'HTTP://Example.com:80/resource?id=123');
	}

/**
 * testSimpleUrlWithEndingSlash
 *
 * @return void
 */
	public function testSimpleUrlWithEndingSlash() {
		$this->assertRequestUrl('http://example.com/', 'http://example.com/');
	}

/**
 * testSimpleUrlWithoutEndingSlash
 *
 * @return void
 */
	public function testSimpleUrlWithoutEndingSlash() {
		$this->assertRequestUrl('http://example.com/', 'http://example.com');
	}

/**
 * testSimpleUrlWithoutEndingSlash
 *
 * @return void
 */
	public function testOfNormalizedHttp() {
		$this->assertRequestUrl('http://example.com/resource', 'http://example.com/resource');
	}

/**
 * testOfHttps
 *
 * @return void
 */
	public function testOfHttps() {
		$this->assertRequestUrl('https://example.com/resource', 'HTTPS://Example.com:443/resource?id=123');
	}

/**
 * testOfNormalizedHttps
 *
 * @return void
 */
	public function testOfNormalizedHttps() {
		$this->assertRequestUrl('https://example.com/resource', 'https://example.com/resource');
	}

/**
 * testOfHttpWithNonStandartPort
 *
 * @return void
 */
	public function testOfHttpWithNonStandartPort() {
		$this->assertRequestUrl('http://example.com:8080/resource', 'http://example.com:8080/resource');
	}

/**
 * testOfHttpsWithNonStandartPort
 *
 * @return void
 */
	public function testOfHttpsWithNonStandartPort() {
		$this->assertRequestUrl('https://example.com:8080/resource', 'https://example.com:8080/resource');
	}

/**
 * assertRequestUrl
 *
 * @param string $expected 
 * @param string $given 
 * @return void
 */
	private function assertRequestUrl($expected, $given) {
		$Request = $this->request(array(), 'GET', $given);
		$this->assertEqual($expected, $Request->normalizedUri());
	}
}
