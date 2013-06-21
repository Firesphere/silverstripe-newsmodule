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
	
	private static $menu_icon = '/silverstripe-newsmodule/images/newspaper.png';
	
	/**
	 * Add the sortorder to tags. I guess tags are sortable now.
	 * @param Int $id (No idea)
	 * @param FieldList $fields because I can
	 * @return Form $form, because it comes in handy.
	 */
	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);
		/**
		 * SortOrder is ignored unless sortable is enabled.
		 */
		if($this->modelClass == "Tag"){
			$form->Fields()
				->fieldByName('Tag')
				->getConfig()
				->addComponent(
					new GridFieldSortableRows(
						'SortOrder'
					)
				);
		}
		return $form;
	}
}

