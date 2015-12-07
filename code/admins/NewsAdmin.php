<?php

/**
 * Default admin for the newsmodule.
 * This way, it's less of a clutter in the sitetree.
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 */
class NewsAdmin extends ModelAdmin
{
	private static $managed_models = array(
		'News',
		'Tag',
	);
	private static $url_segment = 'news';
	private static $menu_title = 'News';
	private static $menu_icon = '/silverstripe-newsmodule/images/newspaper.png';
	public $showImportForm = false;

	/**
	 * Add the sortorder to tags. I guess tags are sortable now.
	 * @param Int $id (No idea)
	 * @param FieldList $fields because I can
	 * @return Form $form, because it comes in handy.
	 */
	public function getEditForm($id = null, $fields = null)
	{
		$form = parent::getEditForm($id, $fields);
		$siteConfig = SiteConfig::current_site_config();
		/**
		 * SortOrder is ignored unless sortable is enabled.
		 */
		if ($this->modelClass === "Tag" && $siteConfig->AllowTags) {
			$form->Fields()
				->fieldByName('Tag')
				->getConfig()
				->addComponent(
					new GridFieldOrderableRows(
						'SortOrder'
					)
				);
		}
		if ($this->modelClass === "News" && !$siteConfig->AllowExport) {
			$form->Fields()
				->fieldByName("News")
				->getConfig()
				->removeComponentsByType('GridFieldExportButton')
				->addComponent(
					new GridfieldNewsPublishAction()
				);
		}

		return $form;
	}

	/**
	 * List only newsitems from current subsite.
	 * @author Marcio Barrientos
	 * @return ArrayList $list
	 */
	public function getList()
	{
		/** @var DataList $list */
		$list = parent::getList();
		if ($this->modelClass === 'News' && class_exists('Subsite') && Subsite::currentSubsiteID() > 0) {
			$pages = NewsHolderPage::get()->filter(array('SubsiteID' => (int)Subsite::currentSubsiteID()));
			$filter = $pages->column('ID');
			/* Manual join needed because otherwise no items are found. Unknown why. */
			$list = $list->innerJoin('NewsHolderPage_Newsitems', 'NewsHolderPage_Newsitems.NewsID = News.ID')
				->filter(array('NewsHolderPage_Newsitems.NewsHolderPageID' => $filter));
		}

		return $list;
	}

	public function subsiteCMSShowInMenu()
	{
		return true;
	}

}
