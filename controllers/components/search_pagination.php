<?php
/**
 * SearchPagination component
 * 
 * Usage:
 *   var $components = array('SearchPagination.SearchPagination');
 * 
 *   function actionMethod() {
 *     $this->SearchPagination->setup('ModelName');
 *     $this->paginate = $this->ModelName->buildQuery();
 *   }
 * 
 * @author Takayuki Miwa <i@tkyk.name>
 * @package SearchPaginationPlugin
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class SearchPaginationComponent extends Object {

    /**
     * @var object  Controller
     */
    protected $_controller;

    /**
     * @var string  helper name
     */
    protected $_helperName = 'SearchPagination.SearchPagination';

    /**
     * initialize callback.
     */
    public function initialize($controller, $setting=array()) {
        $this->_controller = $controller;
    }

    /**
     * Loads and initializes the $searchModel.
     * This method should be called in action methods.
     *
     * @param string  $searchModel
     * @param array   $default     default search parameters
     * @param boolean $useModel    if false, does not load $searchModel
     * @return array
     */
    public function setup($searchModel, $default=array(), $useModel=true) {
        $ctrl = $this->_controller;

        $post = $ctrl->data;
        $get  = $this->__getDataFromURL($searchModel);
        $data = empty($post) ? array($searchModel => empty($get) ? $default : $get) : $post;

        if($useModel) {
            $ctrl->loadModel($searchModel);
            $model = $this->_controller->{$searchModel};
            $model->set($data);
            $ctrl->data = $model->data;
        } else {
            $ctrl->data = $data;
        }

        if(isset($ctrl->data[$searchModel]) && is_array($ctrl->data[$searchModel])) {
            $this->__setupHelper($ctrl->data[$searchModel]);
        }
        return $ctrl->data;
    }

    /**
     * Extracts search parameter array from params['url'].
     * 
     * @param string $searchModel
     * @return array
     */
    private function __getDataFromURL($searchModel) {
        $get = $this->_controller->params['url'];
        unset($get['url']);
        return $get;
    }

    /**
     * Adds SearchPagination.SearchPaginationHelper to the controller::$helpers
     * and passes the search parameters as its options.
     * 
     * @param    array $opts
     */
    private function __setupHelper($opts) {
        $ctrl = $this->_controller;
        if (in_array($this->_helperName, $ctrl->helpers)) {
            unset($ctrl->helpers[array_search($this->_helperName, $ctrl->helpers)]);
        }
        if(!array_key_exists($this->_helperName, $ctrl->helpers)) {
            $ctrl->helpers[$this->_helperName] = array();
        }
        $ctrl->helpers[$this->_helperName]['__search_params'] = $opts;
    }

}
