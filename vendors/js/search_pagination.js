/**
 * @author Takayuki Miwa <i@tkyk.name>
 * @package SearchPaginationPlugin
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
jQuery(function($) {
	$('form.search-pagination :input').each(function(){
		if(this.name == '_method' && this.type == 'hidden') {
		    $(this).remove();
		} else {
		    this.name = this.name.replace(/^data\[[^\]]+\]\[([^\]]+)\](.*)$/, '$1$2');
		}
	    });
	$('form.search-pagination').attr('method', 'GET');
    });
