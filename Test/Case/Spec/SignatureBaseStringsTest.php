<?php 

/*  See http://oauth.net/core/1.0/ *anchor14
 *
 * 9.1.  Signature Base String
 * 
 * The Signature Base String is a consistent reproducible concatenation of the request elements
 * into a single string. The string is used as an input in hashing or signing algorithms. The 
 * HMAC-SHA1 signature method provides both a standard and an example of using the Signature 
 * Base String with a signing algorithm to generate signatures. All the request parameters MUST 
 * be encoded as described in Parameter Encoding prior to constructing the Signature Base String.
 */ 

App::import('File', 'OauthTestCase', true, array(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests'), 'oauth_test_case.php');

class SignatureBaseStringTest  extends OauthTestCase {
  
	public function testA() {
		$parameters = array(
		  'oauth_consumer_key' => 'dpf43f3p2l4k3l03',
		  'oauth_token' => 'nnch734d00sl2jdk',
		  'oauth_signature_method' => 'HMAC-SHA1',
		  'oauth_timestamp' => '1191242096',
		  'oauth_nonce' => 'kllo9940pd9333jh',
		  'oauth_version' => '1.0',
		  'file' => 'vacation.jpg',
		  'size' => 'original');
		$baseString ='GET&http%3A%2F%2Fphotos.example.net%2Fphotos&file%3Dvacation.jpg%26oauth_consumer_key%3Ddpf43f3p2l4k3l03%26oauth_nonce%3Dkllo9940pd9333jh%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1191242096%26oauth_token%3Dnnch734d00sl2jdk%26oauth_version%3D1.0%26size%3Doriginal';

		$this->assertSignatureBaseString($baseString, $parameters, 'GET', "http://photos.example.net/photos");
	}
/*
 * These are from the wiki http://wiki.oauth.net/TestCases
 * in the section Concatenate Test Elements
 */
 
	public function testWikiSimpleWithEndingSlash() {
		$parameters = array('n' => 'v');
		$baseString = 'GET&http%3A%2F%2Fexample.com%2F&n%3Dv';
		$this->assertSignatureBaseString($baseString, $parameters, 'GET', "http://example.com/");
	}


	public function testWikiSimpleWithoutEndingSlash() {
		$parameters = array('n' => 'v');
		$baseString = 'GET&http%3A%2F%2Fexample.com%2F&n%3Dv';
		$this->assertSignatureBaseString($baseString, $parameters, 'GET', "http://example.com");
	}

	public function testWikiRequestToken() {
		$parameters = array(
			'oauth_version' => '1.0',
			'oauth_consumer_key' => 'dpf43f3p2l4k3l03',
			'oauth_timestamp' => '1191242090',
			'oauth_nonce' => 'hsu94j3884jdopsl',
			'oauth_signature_method' => 'PLAINTEXT',
			'oauth_signature' => 'ignored');
		$baseString ='POST&https%3A%2F%2Fphotos.example.net%2Frequest_token&oauth_consumer_key%3Ddpf43f3p2l4k3l03%26oauth_nonce%3Dhsu94j3884jdopsl%26oauth_signature_method%3DPLAINTEXT%26oauth_timestamp%3D1191242090%26oauth_version%3D1.0';

		$this->assertSignatureBaseString($baseString, $parameters, 'POST', "https://photos.example.net/request_token");
	}

	public function assertSignatureBaseString($expected, $params = array(), $method = 'GET', $uri = "http://photos.example.net/photos",$message = "Signature Base String does not match") {
		$this->assertEqual($expected, $this->signatureBaseString($params, $method, $uri), $message);
	}


	public function signatureBaseString($params = array(), $method = 'GET', $uri = "http://photos.example.net/photos") {
		$Request = $this->request($params, $method, $uri);
		return $Request->signatureBaseString();
	}

}
?>