<?php

class OAuthGroupTest extends TestSuite {
 
	var $label = 'OAuth vendor lib tests';

	function OAuthGroupTest() {
		TestManager::addTestCasesFromDirectory($this, APP . 'plugins' . DS . 'oauth_lib' . DS . 'tests' . DS . 'cases' . DS . 'spec');
	}

}
?>