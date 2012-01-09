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
 * Testing specification on Normalize Request Parameters
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.spec
 */
class NormalizeRequestParametersTest extends OauthTestCase {

/**
 * testParametersForSignature
 *
 * @return void
 */
	public function testParametersForSignature() {
		$params = array('a'=>1, 'c'=>'hi there', 'f'=>'25', 'f'=>'50', 'f'=>'a', 'z'=>'p', 'z'=>'t');
		$this->assertEqual($params, $this->request($params)->parametersForSignature());
	}

/**
 * testParametersForSignatureRemovesOauthSignature
 *
 * @return void
 */
	public function testParametersForSignatureRemovesOauthSignature() {
		$params =array('a'=>1, 'c'=>'hi there', 'f'=>'25', 'f'=>'50', 'f'=>'a', 'z'=>'p', 'z'=>'t');
		$this->assertEqual($params, $this->request(array_merge($params, array('oauth_signature'=>'blalbla')))->parametersForSignature());
	}

/**
 * testSpecExample
 *
 * @return void
 */
	public function testSpecExample() {
		$this->assertNormalized('a=1&c=hi%20there&f=25&f=50&f=a&z=p&z=t', array('a' => 1, 'c' => 'hi there', 'f' => array('25', '50', 'a'), 'z' => array('p', 't')));
	}

/**
 * testSortsParametersCorrectly
 *
 * @return void
 */
	public function testSortsParametersCorrectly() {
		$this->assertNormalized('a=1&c=hi%20there&f=5&f=70&f=a&z=p&z=t', array('a' => 1, 'c' => 'hi there', 'f' => array('a', '70', '5'), 'z' => array('p', 't')));
	}

/**
 * testEmpty
 *
 * @return void
 */
	public function testEmpty() {
		$this->assertNormalized('', array());
	}

/**
 * These tests are from the wiki http://wiki.oauth.net/TestCases
 * in the section Normalize Request Parameters
 * Parameters have already been x-www-form-urlencoded (i.e. + = <space>)
 */

/**
 * testWiki1
 *
 * @return void
 */
	public function testWiki1() {
		$this->assertNormalized('name=', array('name' => null));
	}

/**
 * testWiki2
 *
 * @return void
 */
	public function testWiki2() {
		$this->assertNormalized('a=b', array('a' => 'b'));
	}

/**
 * testWiki3
 *
 * @return void
 */
	public function testWiki3() {
		$this->assertNormalized('a=b&c=d', array('a'=>'b', 'c'=>'d'));
	}

/**
 * testWiki4
 *
 * @return void
 */
	public function testWiki4() {
		$this->assertNormalized('a=x%20y&a=x%21y', array('a' => array('x!y', 'x y')));
	}

/**
 * testWiki5
 *
 * @return void
 */
	public function testWiki5() {
		$this->assertNormalized('x=a&x%21y=a', array('x!y' => 'a', 'x' => 'a'));
	}

/**
 * assertNormalized
 *
 * @param string $expected 
 * @param string $params 
 * @return void
 */
	public function assertNormalized($expected, $params) {
		$this->assertEqual($expected, $this->normalizeRequestParameters($params));
	}

/**
 * normalizeRequestParameters
 *
 * @param string $params 
 * @return void
 */
	public function normalizeRequestParameters($params = array()) {
			return $this->request($params)->normalizedParameters();
	}
}
