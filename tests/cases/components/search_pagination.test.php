<?php

App::import('Component', 'SearchPagination.SearchPagination');

class TestControllerForSearchPaginationComponentTestCase
extends Controller {
    var $redirected = false;
    var $redirectUrl;

    function redirect($url) {
        $this->redirected = true;
        $this->redirectUrl = $url;
    }
}

class SearchPaginationComponentTestCase extends CakeTestCase {
    var $s;
    var $c;
    var $url = '/search/pagination';

    function startTest() {
        $this->c = new TestControllerForSearchPaginationComponentTestCase();
        $this->c->params['url']['url'] = $this->url;

        $this->s = new SearchPaginationComponent();
        $this->s->initialize($this->c);
    }

    function endTest() {
        ClassRegistry::flush();
    }

    function assertNotRedirected() {
        $this->assertFalse($this->c->redirected);
    }

    function _setGetParams($arr) {
        $this->c->params['url'] = am($this->c->params['url'], $arr);
    }

    function testInitialize() {
    }

    function testPrg_empty() {
        $model = 'Article';
        $this->assertTrue(empty($this->c->data));
        $this->assertTrue($this->s->prg($model));
        $this->assertNotRedirected();
    }

    function testPrg_emptyParams() {
        $model = 'Article';
        $params = array();
        $this->c->data[$model] = $params;

        $this->assertFalse(empty($this->c->data));
        $this->assertFalse($this->s->prg($model));
        $this->assertIdentical(array(), $this->c->redirectUrl);
    }

    function testPrg_someParams() {
        $model = 'Article';
        $params = array('foo' => 'bar',
                        'baz' => array(1,2,3,'zoo'));
        $this->c->data[$model] = $params;

        $this->assertFalse(empty($this->c->data));
        $this->assertFalse($this->s->prg($model));
        $this->assertIdentical(array('?' => $params),
                               $this->c->redirectUrl);
    }

    function testPrg_someParams_otherModels() {
        $model = 'Article';
        $params = array('foo' => 'bar',
                        'baz' => array(1,2,3,'zoo'));
        $this->c->data[$model] = $params;
        $this->c->data['Another'.$model] = array('not', 'appear');

        $this->assertFalse(empty($this->c->data));
        $this->assertFalse($this->s->prg($model));
        $this->assertIdentical(array('?' => $params),
                               $this->c->redirectUrl);
    }

    function testPrg_someParams_modelMismatch() {
        $model = 'Article';
        $this->c->data['Another'.$model] = array('not', 'appear');

        $this->assertFalse(empty($this->c->data));
        $this->assertFalse($this->s->prg($model));
        $this->assertIdentical(array(), $this->c->redirectUrl);
    }

    function testUnifyData() {
        $model = 'Article';
        $params = array('foo' => 'bar',
                        'baz' => array(1, 2, 3));

        $this->_setGetParams($params);

        $this->s->unifyData($model);
        $this->assertIdentical($params, $this->c->data[$model]);
    }

    function testUnifyData_default() {
        $model = 'Article';

        $this->s->unifyData($model);
        $this->assertIdentical(array(), $this->c->data[$model]);
    }

    function testUnifyData_setDefault() {
        $model = 'Article';
        $default = array('foo' => 'bar',
                         'baz' => array(1, 2, 3));

        $this->s->unifyData($model, $default);
        $this->assertIdentical($default, $this->c->data[$model]);
    }

    function testSetupHelper() {
        $params = array('foo' => 'bar',
                        'baz' => array(1, 2, 3));
        
        $beforeCount = count($this->c->helpers);
        $this->s->setupHelper($params);
        $this->assertIdentical(array('__search_params' => $params),
                               $this->c->helpers[$this->s->helperName]);
        $afterCount = count($this->c->helpers);
        $this->assertEqual($beforeCount + 1, $afterCount);


        $this->c->helpers = array('Html', $this->s->helperName, 'Form');
        $beforeCount = count($this->c->helpers);
        $this->s->setupHelper($params);
        $this->assertIdentical(array('__search_params' => $params),
                               $this->c->helpers[$this->s->helperName]);
        $this->assertFalse(in_array($this->s->helperName,
                                    $this->c->helpers));
        $afterCount = count($this->c->helpers);
        $this->assertEqual($beforeCount, $afterCount);
    }

    function testSetup_Get_NoParams() {
        $model = 'Article';
        $params = array();
        $default = array('foo' => 'bar');

        $this->_setGetParams($params);

        $data = $this->s->setup($model, $default);
        $this->assertIdentical($default, $data);
        $this->assertIdentical($default, $this->c->data[$model]);

        // default parameters are not succeeded!
        $this->assertIdentical(array('__search_params' => array()),
                               $this->c->helpers[$this->s->helperName]);
    }

    function testSetup_Get_someParams() {
        $model = 'Article';
        $params = array('baz' => array(1,2,3),
                        'title' => 'abc');
        $default = array('foo' => 'bar');

        $this->_setGetParams($params);

        $data = $this->s->setup($model, $default);
        $this->assertIdentical($params, $data);
        $this->assertIdentical($params, $this->c->data[$model]);

        // data are succeeded from Controller->params, not ->data.
        $this->assertIdentical(array('__search_params' => $params),
                               $this->c->helpers[$this->s->helperName]);
    }

    function testSetup_Post_someParams() {
        $model = 'Article';
        $params = array('baz' => array(1,2,3),
                        'title' => 'abc');
        $default = array('foo' => 'bar');

        $this->c->data[$model] = $params;

        $data = $this->s->setup($model, $default);
        $this->assertIdentical(array('?' => $params),
                               $this->c->redirectUrl);
    }

    function testSetup_modelClass() {
        $this->c->modelClass = 'Article';
        $params = array('baz' => array(1,2,3),
                        'title' => 'abc');
        $default = array('foo' => 'bar');

        $this->c->data[$this->c->modelClass] = $params;

        $data = $this->s->setup(null, $default);
        $this->assertIdentical(array('?' => $params),
                               $this->c->redirectUrl);
    }




}
