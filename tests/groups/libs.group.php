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

/**
 * Oauth Test case.
 *
 * @package oauth_lib
 * @subpackage oauth_lib.tests.groups
 */
class OAuthGroupTest extends TestSuite {

/**
 * Label
 *
 * @var string
 */
	public $label = 'OAuth vendor lib tests';

/**
 * OAuthGroupTest
 *
 * @return void
 */
	public function OAuthGroupTest() {
		TestManager::addTestCasesFromDirectory($this, APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'libs');
	}
}
