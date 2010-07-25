<?php
/**
 * SearchPagination helper
 *
 * This helper will be automatically loaded by the SearchPaginationComponent.
 *
 * @author Takayuki Miwa <i@tkyk.name>
 * @package SearchPaginationComponent
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class SearchPaginationHelper extends AppHelper {

    /**
     * @var array 
     */
    public $helpers = array('Paginator');

    /**
     * @var array  search parameters passed from SearchPaginationComponent.
     */
    protected $_searchParams;

    /**
     * Constructor
     * 
     * @param array  helper options
     */
    public function __construct($opts=array()) {
        if(!empty($opts['__search_params'])) {
            $this->_searchParams = $opts['__search_params'];
        }
    }

    /**
     * beforeRender callback.
     * 
     * Passes the search parameters to PaginatorHelper.
     */
    public function beforeRender() {
        $this->Paginator->options(array('url' => array('?' => $this->_searchParams)));
    }
}
