<?php
/**
 * Default admin for the newsmodule.
 * This way, it's less of a clutter in the sitetree.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 * @todo Optional support for sortable. Should/could be useful for tags.
 */
class NewsAdmin extends ModelAdmin {

	private static $managed_models = array(
		'News',
		'Tag',
	);

	private static $url_segment = 'news';

	private static $menu_title = 'News';
	
	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		/**
		 * SortOrder is ignored unless sortable is enabled.
		 */
		if($this->modelClass == "Tag"){
			$form->fields
				->items[0]
				->config
				->addComponent(
					new GridFieldSortableRows(
						'SortOrder'
					)
				);
		}
		return $form;
	}
}

