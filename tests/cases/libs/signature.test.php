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

App::import('Lib', 'OauthLib.Signature');
App::import('Lib', 'OauthLib.Consumer');
App::import('Lib', 'OauthLib.ConsumerToken');

/**
 * Oauth Tests
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.libs
 */
class OAuthSignaturePlaintextTest extends CakeTestCase {

/**
 * testThatPlaintextImplemented
 *
 * @return void
 */
	public function testThatPlaintextImplemented() {
		$sig = Signature::getInstance();
		$this->assertTrue(isset($sig->availableMethods['PLAINTEXT']));
	}

/**
 * testGetRequestProduceMatchingSignature
 *
 * @return void
 */
	public function testGetRequestProduceMatchingSignature() {
		$consumer = new Consumer('dpf43f3p2l4k3l03', 'kd94hf93k423kf44', array());
		$ConsumerToken = new ConsumerToken($consumer, 'nnch734d00sl2jdk', null);
		$http = null;
		$request = new ClientHttp($http, '/photos?file=vacation.jpg&size=original&oauth_version=1.0&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_token=nnch734d00sl2jdk&oauth_signature=kd94hf93k423kf44%26&oauth_timestamp=1191242096&oauth_nonce=kllo9940pd9333jh&oauth_signature_method=PLAINTEXT');
		$options = array('consumer' => $consumer, 'token' => $ConsumerToken, 'uri' => 'http://photos.example.net/photos');

		$this->assertTrue(Signature::verify($request, $options));
	}

/**
 * testThatGetRequestFromOauthTestCasesProducesMatchingSignature2
 *
 * @return void
 */
	public function testThatGetRequestFromOauthTestCasesProducesMatchingSignature2() {
		$consumer = new Consumer('dpf43f3p2l4k3l03', 'kd94hf93k423kf44', array());
		$http = null;
		$request = new ClientHttp($http, '/photos?file=vacation.jpg&size=original&oauth_version=1.0&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_token=nnch734d00sl2jdk&oauth_signature=kd94hf93k423kf44%26pfkkdhi9sl3r4s00&oauth_timestamp=1191242096&oauth_nonce=kllo9940pd9333jh&oauth_signature_method=PLAINTEXT');
		$ConsumerToken = new ConsumerToken($consumer, 'nnch734d00sl2jdk', 'pfkkdhi9sl3r4s00');
		$options = array('consumer' => $consumer, 'token' => $ConsumerToken, 'uri' => 'http://photos.example.net/photos');
		$this->assertTrue(Signature::verify($request, $options));
	}
}

/**
 * OAuthSignatureHMACSHA1Test
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.cases.libs
 */
class OAuthSignatureHMACSHA1Test extends CakeTestCase {

/**
 * testThatHmacSha1ImplementsHmacSha1
 *
 * @return void
 */
	public function testThatHmacSha1ImplementsHmacSha1() {
		$sig = Signature::getInstance();
		$this->assertTrue(isset($sig->availableMethods['HMAC-SHA1']));
	}

/**
 * testThatGetRequestFromOauthTestCasesProducesMatchingSignature
 *
 * @return void
 */
	public function testThatGetRequestFromOauthTestCasesProducesMatchingSignature() {
		$http = null;
		$request = & new ClientHttp($http, '/photos?file=vacation.jpg&size=original&oauth_version=1.0&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_token=nnch734d00sl2jdk&oauth_timestamp=1191242096&oauth_nonce=kllo9940pd9333jh&oauth_signature_method=HMAC-SHA1');
		$consumer = new Consumer('dpf43f3p2l4k3l03', 'kd94hf93k423kf44', array());
		$ConsumerToken = new ConsumerToken($consumer, 'nnch734d00sl2jdk', 'pfkkdhi9sl3r4s00');
		$options = array('consumer' => $consumer, 'token' => $ConsumerToken, 'uri' => 'http://photos.example.net/photos');
		$signature = Signature::sign($request, $options);
		$this->assertEqual('tR3+Ty81lMeYAr/Fid0kMTYa/WM=', $signature);
	}
}

/**
 * OAuthSignatureRsaSHA1Test
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.cases.libs
 */
class OAuthSignatureRsaSHA1Test extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setup() {
		App::import('Core', 'File');
		$file = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.pem');
		$this->pem = $file->read();
		$cert = new File(APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'fixtures' . DS . 'certificates' . DS . 'termie.cer');
		$this->cert = $cert->read();
		$this->consumer = new Consumer('dpf43f3p2l4k3l03', $this->pem,
			array());
		$consumer = new Consumer('dpf43f3p2l4k3l03', 'kd94hf93k423kf44', array());
		$this->ConsumerToken = new ConsumerToken($this->consumer, '', '');
		$http = null;
		$this->request = & new ClientHttp($http, '/photos?file=vacaction.jpg&size=original&oauth_version=1.0&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_timestamp=1196666512&oauth_nonce=13917289812797014437&oauth_signature_method=RSA-SHA1');
	}

/**
 * testThatHmacSha1ImplementsRsaSha1
 *
 * @return void
 */
	public function testThatHmacSha1ImplementsRsaSha1() {
		$sig = Signature::getInstance();
		$this->assertTrue(isset($sig->availableMethods['RSA-SHA1']));
	}

/**
 * testGetRequestSignatureBaseString
 *
 * @return void
 */
	public function testGetRequestSignatureBaseString() {
		$options = array(
			'consumer' => $this->consumer,
			'token' => $this->ConsumerToken,
			'uri' => 'http://photos.example.net/photos',
			'privateCert' => '',
			'publicCert' => '',
			'privateCertPass' => '');
		$signatureString = Signature::signatureBaseString($this->request, $options);
		$this->assertEqual('GET&http%3A%2F%2Fphotos.example.net%2Fphotos&file%3Dvacaction.jpg%26oauth_consumer_key%3Ddpf43f3p2l4k3l03%26oauth_nonce%3D13917289812797014437%26oauth_signature_method%3DRSA-SHA1%26oauth_timestamp%3D1196666512%26oauth_version%3D1.0%26size%3Doriginal', $signatureString);
	}

/**
 * testGetRequestSignature
 *
 * @return void
 */
	public function testGetRequestSignature() {
		$options = array(
			'consumer' => $this->consumer,
			'token' => $this->ConsumerToken,
			'uri' => 'http://photos.example.net/photos',
			'privateCert' => $this->pem,
			'publicCert' => '',
			'privateCertPass' => '');
		$signature = Signature::sign($this->request, $options);
		$this->assertEqual('jvTp/wX1TYtByB1m+Pbyo0lnCOLIsyGCH7wke8AUs3BpnwZJtAuEJkvQL2/9n4s5wUmUl4aCI4BwpraNx4RtEXMe5qg5T1LVTGliMRpKasKsW//e+RinhejgCuzoH26dyF8iY2ZZ/5D1ilgeijhV/vBka5twt399mXwaYdCwFYE=', $signature);
	}

/**
 * testGetRequestVerify
 *
 * @return void
 */
	public function testGetRequestVerify() {
		$http = null;
		$request = & new ClientHttp($http, '/photos?oauth_signature_method=RSA-SHA1&oauth_version=1.0&oauth_consumer_key=dpf43f3p2l4k3l03&oauth_timestamp=1196666512&oauth_nonce=13917289812797014437&file=vacaction.jpg&size=original&oauth_signature=jvTp%2FwX1TYtByB1m%2BPbyo0lnCOLIsyGCH7wke8AUs3BpnwZJtAuEJkvQL2%2F9n4s5wUmUl4aCI4BwpraNx4RtEXMe5qg5T1LVTGliMRpKasKsW%2F%2Fe%2BRinhejgCuzoH26dyF8iY2ZZ%2F5D1ilgeijhV%2FvBka5twt399mXwaYdCwFYE%3D');
		$consumer = new Consumer('dpf43f3p2l4k3l03', $this->cert, array());
		$ConsumerToken = new ConsumerToken($consumer, '', '');

		$options = array(
			'consumer' => $consumer,
			'token' => $ConsumerToken,
			'uri' => 'http://photos.example.net/photos',
			'privateCert' => '',
			'publicCert' => $this->cert,
			'privateCertPass' => '');
		$result = Signature::verify($request, $options);
		$this->assertTrue($result);
	}
}
