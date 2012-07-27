<?php
class AllOauthPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All Oauth Plugin Tests');

		$basePath = CakePlugin::path('OauthLib') . 'Test' . DS . 'Case' . DS;
		$libs = $basePath . 'Lib' . DS;
		$tokens = $libs . 'Token' . DS;
		$spec = $basePath . 'Spec' . DS;

		// Libs
		$suite->addTestFile($libs . 'ClientHttpGoogleTest.php');
		$suite->addTestFile($libs . 'ClientHttpTermieTest.php');
		$suite->addTestFile($libs . 'ClientHttpTest.php');
		$suite->addTestFile($libs . 'ConsumerGoogleTest.php');
		$suite->addTestFile($libs . 'ConsumerTest.php');
		$suite->addTestFile($libs . 'HmacTest.php');
		$suite->addTestFile($libs . 'RequestFactoryTest.php');
		$suite->addTestFile($libs . 'SignatureTest.php');

		// Libs/Token
		$suite->addTestFile($tokens . 'AccessTokenTest.php');
		$suite->addTestFile($tokens . 'RequestTokenTest.php');
		$suite->addTestFile($tokens . 'TokenTest.php');

		// Libs/RequestProxy
		$suite->addTestFile($libs . 'RequestProxy' . DS . 'RequestProxyControllerTest.php');
		$suite->addTestFile($libs . 'RequestProxy' . DS . 'RequestProxyHttpTest.php');

		// Spec
		$suite->addTestFile($spec . 'ConstructRequestUrlTest.php');
		$suite->addTestFile($spec . 'NormalizeRequestParametersTest.php');
		$suite->addTestFile($spec . 'SignatureBaseStringsTest.php');

		return $suite;
	}

}