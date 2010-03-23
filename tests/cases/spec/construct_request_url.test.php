<?php 

/**
 * See http://oauth.net/core/1.0/#anchor14
 *
 *  9.1.2.  Construct Request URL
 *
 * The Signature Base String includes the request absolute URL,  tying the signature to a specific endpoint. 
 * The URL used in the Signature Base String MUST include the scheme, authority, and path, and MUST exclude 
 * the query and fragment as defined by [RFC3986] section 3.
 *
 * If the absolute request URL is not available to the Service Provider (it is always available to the
 * Consumer), it can be constructed by combining the scheme being used, the HTTP Host header, 
 * and the relative HTTP request URL. If the Host header is not available,  the Service Provider SHOULD 
 * use the host name communicated to the Consumer in the documentation or other means.
 *
 * The Service Provider SHOULD document the form of URL used in the Signature Base String to avoid ambiguity 
 * due to URL normalization. Unless specified, URL scheme and authority MUST be lowercase and include 
 * the port number; http default port 80 and https default port 443 MUST be excluded.
 * For example, the request:
 *
 *  HTTP://Example.com:80/resource?id=123
 * Is included in the Signature Base String as:
 *
 *         http://example.com/resource
 */

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

class ConstructRequestUrlTest extends OauthTestCase {
  
	public function test1() {
		$this->assertRequestUrl('http://example.com/resource', 'HTTP://Example.com:80/resource?id=123');
	}

	public function testSimpleUrlWithEndingSlash() {
		$this->assertRequestUrl('http://example.com/', 'http://example.com/');
	}

	public function testSimpleUrlWithoutEndingSlash() {
		$this->assertRequestUrl('http://example.com/', 'http://example.com');
	}

	public function testOfNormalizedHttp() {
		$this->assertRequestUrl('http://example.com/resource', 'http://example.com/resource');
	}

	public function testOfHttps() {
		$this->assertRequestUrl('https://example.com/resource', 'HTTPS://Example.com:443/resource?id=123');
	}

	public function testOfNormalizedHttps() {
		$this->assertRequestUrl('https://example.com/resource', 'https://example.com/resource');
	}

	public function testOfHttpWithNonStandartPort() {
		$this->assertRequestUrl('http://example.com:8080/resource', 'http://example.com:8080/resource');
	}

	public function testOfHttpsWithNonStandartPort() {
		$this->assertRequestUrl('https://example.com:8080/resource', 'https://example.com:8080/resource');
	}

	private function assertRequestUrl($expected, $given) {
		$Request = $this->request(array(), 'GET', $given);
		$this->assertEqual($expected, $Request->normalizedUri());
	}

}
?>