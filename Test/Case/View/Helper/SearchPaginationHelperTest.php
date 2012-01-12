<?php

App::import('Helper', 'Paginator');
App::import('Helper', 'SearchPagination.SearchPagination');

Mock::generate('PaginatorHelper');

class SearchPaginationHelperTestCase extends CakeTestCase {

	public $h, $p;

	public function startTest() {
		$this->p = new MockPaginatorHelper();
	}

	public function endTest() {
		unset($this->h);
		ClassRegistry::flush();
	}

	protected function _init($params) {
		$this->h = new SearchPaginationHelper(array('__search_params' => $params));
		$this->h->Paginator = $this->p;
	}

	public function testBeforeRender() {
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3));
		$this->_init($params);

		$this->p->expectOnce('options', array(array('url' => array('?' => $params))));
		$this->h->beforeRender();
	}

	public function testBeforeRender_empty() {
		$params = array();
		$this->_init($params);

		$this->p->expectNever('options');
		$this->h->beforeRender();
	}

}
