<?php

class AllSearchPaginationPluginTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All Search Pagination Plugin Tests');
		$suite->addTestDirectoryRecursive(__DIR__ . DS . 'Controller');
		$suite->addTestDirectoryRecursive(__DIR__ . DS . 'View');
		return $suite;
	}

}
