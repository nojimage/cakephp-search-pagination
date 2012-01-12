<?php

App::uses('SearchPaginationComponent', 'SearchPagination.Controller/Component');
App::uses('Controller', 'Controller');
App::uses('Router', 'Routing');

/**
 * @property SearchPaginationComponent $SearchPagination
 */
class TestControllerForSearchPaginationComponentTestCase extends Controller {

	public $uses = array();

	public $components = array(
		'SearchPagination.SearchPagination',
	);

	public $redirected = false;

	public $redirectUrl;

	public function redirect($url) {
		$this->redirected = true;
		$this->redirectUrl = $url;
	}

}

/**
 * @property TestControllerForSearchPaginationComponentTestCase $c
 */
class SearchPaginationComponentTest extends CakeTestCase {

	public $url = '/search/pagination';

	protected $_escapedGet;

	public function setUp() {
		parent::setUp();
		$this->c = new TestControllerForSearchPaginationComponentTestCase();
		$this->c->constructClasses();
		// set 'ext' parameter
		if (preg_match('/parseExtensions/i', $this->getName())) {
			Router::parseExtensions();
		}
		Router::connect('/:controller/:action/*');
		$this->c->request->params = Router::parse($this->url);
		$this->c->request->url = $this->url;
		$this->c->request->query = array();
	}

	public function tearDown() {
		unset($this->controller);
		parent::tearDown();
	}

	public function assertNotRedirected() {
		$this->assertFalse($this->c->redirected);
	}

	protected function _setGetParams($arr) {
		$this->c->request->query = am($this->c->request->query, $arr);
	}

	public function testPrg_empty() {
		$model = 'Article';
		$this->assertTrue(empty($this->c->request->data));
		$this->assertTrue($this->c->SearchPagination->prg($model));
		$this->assertNotRedirected();
	}

	public function testPrg_emptyParams() {
		$model = 'Article';
		$params = array();
		$this->c->request->data[$model] = $params;
		$this->assertFalse(empty($this->c->request->data));
		$this->assertFalse($this->c->SearchPagination->prg($model));
		$this->assertIdentical(array(), $this->c->redirectUrl);
	}

	public function testPrg_someParams() {
		$model = 'Article';
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3, 'zoo'));
		$this->c->request->data[$model] = $params;

		$this->assertFalse(empty($this->c->request->data));
		$this->assertFalse($this->c->SearchPagination->prg($model));
		$this->assertIdentical(array('?' => $params), $this->c->redirectUrl);
	}

	public function testPrg_someParams_otherModels() {
		$model = 'Article';
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3, 'zoo'));
		$this->c->request->data[$model] = $params;
		$this->c->request->data['Another' . $model] = array('not', 'appear');

		$this->assertFalse(empty($this->c->request->data));
		$this->assertFalse($this->c->SearchPagination->prg($model));
		$this->assertIdentical(array('?' => $params), $this->c->redirectUrl);
	}

	public function testPrg_someParams_modelMismatch() {
		$model = 'Article';
		$this->c->request->data['Another' . $model] = array('not', 'appear');

		$this->assertFalse(empty($this->c->request->data));
		$this->assertFalse($this->c->SearchPagination->prg($model));
		$this->assertIdentical(array(), $this->c->redirectUrl);
	}

	public function testUnifyData() {
		$model = 'Article';
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3));

		$this->_setGetParams($params);

		$this->c->SearchPagination->unifyData($model);
		$this->assertIdentical($params, $this->c->request->data[$model]);
	}

	public function testUnifyData_default() {
		$model = 'Article';

		$this->c->SearchPagination->unifyData($model);
		$this->assertIdentical(array(), $this->c->request->data[$model]);
	}

	public function testUnifyData_setDefault() {
		$model = 'Article';
		$default = array('foo' => 'bar',
			'baz' => array(1, 2, 3));

		$this->c->SearchPagination->unifyData($model, $default);
		$this->assertIdentical($default, $this->c->request->data[$model]);
	}

	public function testSetupHelper() {
		$params = array('foo' => 'bar',
			'baz' => array(1, 2, 3));

		$beforeCount = count($this->c->helpers);
		$this->c->SearchPagination->setupHelper($params);
		$this->assertIdentical(array('__search_params' => $params), $this->c->helpers[$this->c->SearchPagination->settings['helperName']]);
		$afterCount = count($this->c->helpers);
		$this->assertEqual($beforeCount + 1, $afterCount);

		$this->c->helpers = array('Html', $this->c->SearchPagination->settings['helperName'], 'Form');
		$beforeCount = count($this->c->helpers);
		$this->c->SearchPagination->setupHelper($params);
		$this->assertIdentical(array('__search_params' => $params), $this->c->helpers[$this->c->SearchPagination->settings['helperName']]);
		$this->assertFalse(in_array($this->c->SearchPagination->settings['helperName'], $this->c->helpers));
		$afterCount = count($this->c->helpers);
		$this->assertEqual($beforeCount, $afterCount);
	}

	public function testSetup_Get_NoParams() {
		$model = 'Article';
		$params = array();
		$default = array('foo' => 'bar');

		$this->_setGetParams($params);

		$data = $this->c->SearchPagination->setup($model, $default);
		$this->assertIdentical($default, $data);
		$this->assertIdentical($default, $this->c->request->data[$model]);

		// default parameters are not succeeded!
		$this->assertIdentical(array('__search_params' => array()), $this->c->helpers[$this->c->SearchPagination->settings['helperName']]);
	}

	public function testSetup_Get_NoParams_When_Router_ParseExtensions() {
		$this->testSetup_Get_NoParams();
	}

	public function testSetup_Get_someParams() {
		$model = 'Article';
		$params = array('baz' => array(1, 2, 3),
			'title' => 'abc');
		$default = array('foo' => 'bar');

		$this->_setGetParams($params);

		$data = $this->c->SearchPagination->setup($model, $default);
		$this->assertIdentical($params, $data);
		$this->assertIdentical($params, $this->c->request->data[$model]);

		// data are succeeded from Controller->params, not ->data.
		$this->assertIdentical(array('__search_params' => $params), $this->c->helpers[$this->c->SearchPagination->settings['helperName']]);
	}

	public function testSetup_Get_someParams_When_Router_ParseExtensions() {
		$this->testSetup_Get_someParams();
	}

	public function testSetup_Post_someParams() {
		$model = 'Article';
		$params = array('baz' => array(1, 2, 3),
			'title' => 'abc');
		$default = array('foo' => 'bar');

		$this->c->request->data[$model] = $params;

		$data = $this->c->SearchPagination->setup($model, $default);
		$this->assertIdentical(array('?' => $params), $this->c->redirectUrl);
	}

	public function testSetup_modelClass() {
		$this->c->modelClass = 'Article';
		$params = array('baz' => array(1, 2, 3),
			'title' => 'abc');
		$default = array('foo' => 'bar');

		$this->c->request->data[$this->c->modelClass] = $params;

		$data = $this->c->SearchPagination->setup(null, $default);
		$this->assertIdentical(array('?' => $params), $this->c->redirectUrl);
	}

}
