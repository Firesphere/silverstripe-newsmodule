<?php
/**
 * Default admin for the newsmodule.
 * This way, it's less of a clutter in the sitetree.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 */
class NewsAdmin extends ModelAdmin {

	public static $managed_models = array(
		'News',
	);

	public static $url_segment = 'news';

	public static $menu_title = 'News';

}

