<?php
/* See http://oauth.net/core/1.0/#anchor14
 *
 * 9.1.1.  Normalize Request Parameters
 * 
 * The request parameters are collected, sorted and concatenated into a normalized string:
 * 
 * Parameters in the OAuth HTTP Authorization header excluding the realm parameter.
 * Parameters in the HTTP POST request body (with a content-type of application/x-www-form-urlencoded).
 * HTTP GET parameters added to the URLs in the query part (as defined by [RFC3986] section 3).
 * The oauth_signature parameter MUST be excluded.
 * 
 * The parameters are normalized into a single string as follows:
 * 
 * Parameters are sorted by name, using lexicographical byte value ordering. 
 * If two or more parameters share the same name, they are sorted by their value. For example:
 *
 *   a=1, c=hi%20there, f=25, f=50, f=a, z=p, z=t
 * Parameters are concatenated in their sorted order into a single string. For each parameter, 
 * the name is separated from the corresponding value by an ‘=’ character (ASCII code 61), even 
 * if the value is empty. Each name-value pair is separated by an ‘&’ character (ASCII code 38). For example:
 *   a=1&c=hi%20there&f=25&f=50&f=a&z=p&z=t
 */

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

class NormalizeRequestParametersTest extends OauthTestCase {

	public function testParametersForSignature() {
		$params = array('a'=>1, 'c'=>'hi there', 'f'=>'25', 'f'=>'50', 'f'=>'a', 'z'=>'p', 'z'=>'t');
		$this->assertEqual($params, $this->request($params)->parametersForSignature());
	}

	public function testParametersForSignatureRemovesOauthSignature() {
		$params =array('a'=>1, 'c'=>'hi there', 'f'=>'25', 'f'=>'50', 'f'=>'a', 'z'=>'p', 'z'=>'t');
		$this->assertEqual($params, $this->request(array_merge($params, array('oauth_signature'=>'blalbla')))->parametersForSignature());
	}

	public function testSpecExample() {
		$this->assertNormalized('a=1&c=hi%20there&f=25&f=50&f=a&z=p&z=t', array('a' => 1, 'c' => 'hi there', 'f' => array('25', '50', 'a'), 'z' => array('p', 't')));
	}

	public function testSortsParametersCorrectly() {
		$this->assertNormalized('a=1&c=hi%20there&f=5&f=70&f=a&z=p&z=t', array('a' => 1, 'c' => 'hi there', 'f' => array('a', '70', '5'), 'z' => array('p', 't')));
	}

	public function testEmpty() {
		$this->assertNormalized('', array());
	}

/**
 * These are from the wiki http://wiki.oauth.net/TestCases
 * in the section Normalize Request Parameters
 * Parameters have already been x-www-form-urlencoded (i.e. + = <space>)
 */
	public function testWiki1() {
		$this->assertNormalized('name=', array('name' => null));
	}

	public function testWiki2() {
		$this->assertNormalized('a=b', array('a' => 'b'));
	}

	public function testWiki3() {
		$this->assertNormalized('a=b&c=d', array('a'=>'b', 'c'=>'d'));
	}

	public function testWiki4() {
		$this->assertNormalized('a=x%20y&a=x%21y', array('a' => array('x!y', 'x y')));
	}

	public function testWiki5() {
		$this->assertNormalized('x=a&x%21y=a', array('x!y' => 'a', 'x' => 'a'));
	}

	public function assertNormalized($expected, $params) {
		$this->assertEqual($expected, $this->normalizeRequestParameters($params));
	}

	public function normalizeRequestParameters($params = array()) {
			return $this->request($params)->normalizedParameters();
	}
}

?>