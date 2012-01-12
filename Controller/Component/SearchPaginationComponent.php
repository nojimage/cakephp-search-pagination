<?php
/**
 * SearchPagination component
 * 
 * Usage:
 * <code>
 *   var $uses = array('ModelName');
 *   var $components = array('SearchPagination.SearchPagination');
 * 
 *   function actionMethod() {
 *     $data = $this->SearchPagination->setup('ModelName');
 *     $this->paginate['conditions'] = $this->ModelName->parseCriteria($data);
 *     $this->set('models', $this->paginate());
 *   }
 * </code>
 * 
 * @author Takayuki Miwa <i@tkyk.name>
 * @package SearchPagination
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::uses('Component', 'Controller');

class SearchPaginationComponent extends Component {

/**
 * @var Controller
 */
	protected $_controller;

/**
 * @var array default options
 */
	protected $_defaults = array(
		'helperName' => 'SearchPagination.SearchPagination'
	);

/**
 * Constructor
 *
 * @param object Controller object
 */
	public function __construct(ComponentCollection $collection, $settings) {
		$this->_controller = $collection->getController();
		$this->settings = Set::merge($this->_defaults, $settings);
	}

/**
 * This method should be called in action methods.
 *
 * @param string  $searchModel
 * @param array   $default     default search parameters
 * @return array
 */
	public function setup($searchModel=null, $default=array()) {
		if (empty($searchModel)) {
			$searchModel = $this->_controller->modelClass;
		}
		if ($this->prg($searchModel)) {
			$this->unifyData($searchModel, $default);
			$this->setupHelper($this->__extractGetParams());
			return $this->_controller->request->data[$searchModel];
		}
	}

/**
 * Post-Redirect-Get pattern
 * 
 * @param string  model name
 * @return boolean  true if no need to redirect
 */
	public function prg($modelName) {
		if (empty($this->_controller->request->data)) {
			return true;
		}
		$url = empty($this->_controller->request->data[$modelName]) ?
			array() :
			array('?' => $this->_controller->request->data[$modelName]);
		$this->_controller->redirect($url);
		return false;
	}

/**
 * Extracts search parameters from params['url']
 * and stores them into Controller->data.
 * 
 * @param string  model name
 * @param array   default parameters
 */
	public function unifyData($modelName, $default=array()) {
		$params = $this->__extractGetParams();
		$this->_controller->request->data[$modelName]
			= empty($params) ? $default : $params;
	}

/**
 * Extracts search parameters from params['url'] and returns them.
 * 
 * @return array
 */
	private function __extractGetParams() {
		return $this->_controller->request->query;
	}

/**
 * Adds SearchPagination.SearchPaginationHelper to the Controller->$helpers
 * passing the search parameters to its options.
 * 
 * @param array  search parameters
 */
	public function setupHelper($params) {
		$ctrl = $this->_controller;
		$helperName = $this->settings['helperName'];

		if (in_array($helperName, $ctrl->helpers)) {
			unset($ctrl->helpers[array_search($helperName, $ctrl->helpers)]);
		}
		if (!array_key_exists($helperName, $ctrl->helpers)) {
			$ctrl->helpers[$helperName] = array();
		}
		$ctrl->helpers[$helperName]['__search_params'] = $params;
	}

}
