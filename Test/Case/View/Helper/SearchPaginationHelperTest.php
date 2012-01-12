<?php

App::uses('View', 'View');
App::uses('PaginatorHelper', 'View/Helper');
App::uses('SearchPaginationHelper', 'SearchPagination.View/Helper');

/**
 * @property View $v
 * @property PaginatorHelper $p
 * @property SearchPaginationHelper $h
 */
class SearchPaginationHelperTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
		$this->v = new View(null);
		$this->p = $this->getMock('PaginatorHelper', array('options'), array($this->v));
	}

	public function tearDown() {
		unset($this->p);
		unset($this->h);
		parent::tearDown();
	}

	protected function _init($params) {
		$this->h = new SearchPaginationHelper($this->v, array('__search_params' => $params));
		$this->h->Paginator = $this->p;
	}

	public function testBeforeRender() {
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3));
		$this->_init($params);

		$this->p->expects($this->once())->method('options')
			->with($this->equalTo(
					array('url' => array('?' => $params))
				));
		$this->h->beforeRender();
	}

	public function testBeforeRender_empty() {
		$params = array();
		$this->_init($params);

		$this->p->expects($this->never())->method('options');
		$this->h->beforeRender();
	}

}
