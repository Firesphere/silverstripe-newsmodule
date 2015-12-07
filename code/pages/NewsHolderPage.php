<?php

/**
 * News page and controller.
 *
 * NewsHolderPage
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 * @todo besides the general getters, the news-functions should be in the model
 *
 * StartGeneratedWithDataObjectAnnotator
 * @property boolean IsPrimary
 * @method ManyManyList|News[] Newsitems
 * EndGeneratedWithDataObjectAnnotator
 */
class NewsHolderPage extends Page
{
	/** @var string $description PageType's description */
	private static $description = 'HolderPage for newsitems';
	private static $db = array(
		'IsPrimary' => 'Boolean(false)',
	);

	/** @var array $many_many many-to-many relationships */
	private static $many_many = array(
		'Newsitems' => 'News'
	);

	/** @var array $allowed_children Allowed Children */
	private static $allowed_children = array(
		'News',
	);

	/**
	 * Create a default NewsHolderPage. This prevents error500 because of a missing page.
	 */
	public function requireDefaultRecords()
	{
		parent::requireDefaultRecords();
		if (NewsHolderPage::get()->count() === 0) {
			/** @var NewsHolderPage $page */
			$page = NewsHolderPage::create();
			$page->Title = _t('NewsHolderPage.DEFAULTPAGETITLE', 'Newspage');
			$page->Content = '';
			$page->URLSegment = 'news';
			$page->Sort = 1;
			$page->Status = 'Published';
			$page->IsPrimary = true;
			$page->write();
			$page->publish('Stage', 'Live');
			$page->flushCache();
			DB::alteration_message('Newsholder Page created', 'created');
		} else {
			/** Migration is only possible, if the module is installed. We're assuming, this means there's at least one Holderpage. */
			$this->migrateUp();
		}
	}

	private function migrateUp()
	{
		$this->migratePublish();
		$this->migrateAuthors();
		$this->migratePages();
		$this->migrateOrphans();
	}

	/**
	 * Migrate the Publish feature form one of the first versions.
	 * This old version didn't work with PublishFrom but with Created.
	 * So, we update here, to set the PublishFrom to the Created value.
	 */
	private function migratePublish()
	{
		/** Backwards compatibility for upgraders. Update the PublishFrom field */
		$sql = "UPDATE `News` SET `PublishFrom` = `Created` WHERE `PublishFrom` IS NULL";
		DB::query($sql);
	}

	/**
	 * For each author, add an AuthorHelper
	 */
	private function migrateAuthors()
	{
		/** @var SQLSelect $query */
		$query = SQLSelect::create();
		$query->setSelect('Author')
			->setFrom('News')
			->setDistinct(true);
		$authors = $query->execute();
		foreach ($authors as $author) {
			/** Create a new author if it doesn't exist */
			if (!$authorHelper = AuthorHelper::get()->filter(array('OriginalName' => trim($author['Author'])))->first()) {
				/** @var AuthorHelper $authorHelper */
				$authorHelper = AuthorHelper::create();
				$authorHelper->OriginalName = $author['Author'];
				$authorHelper->write();
			}
			$sql = "UPDATE `News` SET `AuthorHelperID` = '" . $authorHelper->ID . "' WHERE Author = '" . $author['Author'] . "'";
			DB::query($sql);
		}
	}

	/**
	 * This is to migrate existing newsitems to the new release with the new relational method.
	 * It is forward-non-destructive.
	 * Only run if there is a column NewsHolderPageID
	 * @todo This needs a rewrite, could be done with less queries with an add Items to Page instead of the current situation.
	 */
	private function migratePages()
	{
		$existquery = "SHOW COLUMNS FROM `News` LIKE 'NewsHolderPageID';";
		/** @var DB $exists */
		$exists = DB::query($existquery);
		if ($count = $exists->numRecords()) {
			/** @var SQLQuery $query */
			$query = new SQLQuery();
			$query->setSelect(array('ID', 'NewsHolderPageID'))
				->setFrom('News');
			$newsitems = $query->execute();
			foreach ($newsitems as $newsitem) {
				if ($newsitem['NewsHolderPageID'] && NewsHolderPage::get()->byID($newsitem['NewsHolderPageID'])) {
					News::get()
						->byID($newsitem['ID'])
						->NewsHolderPages()->add($newsitem['NewsHolderPageID']);
				}
			}
		}
	}

	/**
	 * Migrate orphanaged newsitems.
	 * @todo make this work as wished. As it's doing.... not so very much
	 */
	private function migrateOrphans()
	{

	}

	/**
	 * Support for children.
	 * Just call <% loop Children.Limit(x) %>$Title<% end_loop %> from your template to get the news-children.
	 * @return ArrayList NewsItems belonging to this page
	 */
	public function Children()
	{
		$now = SS_DateTime::now()->Format('Y-m-d');

		return $this->Newsitems()
			->filter(array('Live' => true))
			->exclude(array('PublishFrom:GreaterThan' => $now));
	}

}

class NewsHolderPage_Controller extends Page_Controller
{
	private static $allowed_actions = array(
		'show',
		'tag',
		'tags',
		'rss',
		'archive',
		'author',
		'CommentForm',
		'migrate',
		'latest',
	);
	private static $url_handlers = array();
	protected $current_item;
	protected $current_tag;
	protected $current_siteconfig;

	/**
	 * Setup the allowed actions to work with the SiteConfig settings.
	 * @param string $limitToClass
	 * @return array
	 */
	public function allowedActions($limitToClass = null)
	{
		$actions = parent::allowedActions($limitToClass);
		$defaultMapping = $this->stat('allowed_actions');
		$siteConfig = $this->getCurrentSiteConfig();
		foreach ($defaultMapping as $map) {
			$key = ucfirst($map . 'Action');
			if ($siteConfig->$key) {
				self::$allowed_actions[] = $siteConfig->$key;
			}
		}
		if (is_array($actions)) {
			return array_merge($actions, self::$allowed_actions);
		}

		return self::$allowed_actions;
	}

	/**
	 * Setup the handling of the actions. This is needed for the custom URL Actions set in the SiteConfig
	 * @param SS_Request $request The given request
	 * @param string $action The requested action
	 * @return parent::handleAction
	 */
	public function handleAction($request, $action)
	{
		$handles = parent::allowedActions(false);
		$defaultMapping = $this->stat('allowed_actions');
		$handles['index'] = 'handleIndex';
		$siteConfig = $this->getCurrentSiteConfig();
		foreach ($defaultMapping as $key) {
			$map = ucfirst($key . 'Action');
			if ($siteConfig->$map) {
				$handles[$siteConfig->$map] = $key;
			}
			if (!array_key_exists($key, $handles)) {
				$handles[$key] = $key;
			}
		}
		self::$url_handlers = $handles;
		$this->needsRedirect();

		return parent::handleAction($request, $handles[$action]);
	}

	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 */
	public function init()
	{
		parent::init();
		// I would advice to put these in a combined file, but it works this way too.
		Requirements::javascript('silverstripe-newsmodule/javascript/jquery.tagcloud.js');
		Requirements::javascript('silverstripe-newsmodule/javascript/newsmodule.js');
	}

	/**
	 * Set the current newsitem, if available.
	 */
	private function setNews()
	{
		$Params = $this->getURLParams();
		/** @var array $segmentFilter Array containing the filter for current or any item */
		$segmentFilter = $this->setupFilter($Params);
		$news = $this->Newsitems()
			->filter($segmentFilter)
			->exclude(array('PublishFrom:GreaterThan' => SS_Datetime::now()->format('Y-m-d')))
			->first();
		$this->current_item = $news;
	}

	/**
	 * Get the current newsitem
	 * @return News The current newsitem
	 */
	public function getNews()
	{
		if (!$this->current_item) {
			$this->setNews();
		}

		return $this->current_item;
	}

	/**
	 * Set the current tag
	 */
	private function setTag()
	{
		$Params = $this->getURLParams();
		$tag = Tag::get()
			->filter(array('URLSegment' => Convert::raw2sql($Params['ID'])))->first();
		$this->current_tag = $tag;
	}

	/**
	 * Get the current tag.
	 * @todo Implement translations?
	 * @return Tag with tags or news.
	 */
	public function getTag()
	{
		if (!$this->current_tag) {
			$this->setTag();
		}

		return $this->current_tag;
	}

	/**
	 * Set the current SiteConfig
	 */
	private function setCurrentSiteConfig()
	{
		$this->current_siteconfig = SiteConfig::current_site_config();
	}

	/**
	 * Get the current SiteConfig
	 * @return SiteConfig
	 */
	public function getCurrentSiteConfig()
	{
		if (!$this->current_siteconfig) {
			$this->setCurrentSiteConfig();
		}

		return $this->current_siteconfig;
	}

	public function getCurrentAuthor()
	{
		$id = $this->getRequest()->param('ID');

		return AuthorHelper::get()->filter(array('URLSegment' => $id))->first();
	}

	/**
	 * This feature is cleaner for redirection.
	 * Saves requests to the database if I'm not mistaken.
	 * @return $this|null redirect to either the correct page/object or do nothing (In that case, the item exists and we're gonna show it lateron).
	 */
	private function needsRedirect()
	{
		/** @var int|string $id */
		$id = $this->getRequest()->param('ID');
		/** @var string $action */
		$action = $this->getRequest()->param('Action');
		/** @var array $handlers */
		$handlers = self::$url_handlers;
		if (array_key_exists($action, $handlers) && $handlers[$action] === 'show' && !$news = $this->getNews()) {
			if ($id && is_numeric($id)) {
				/** @var News $redirect */
				$redirect = $this->Newsitems()->byId($id);
				$this->redirect($redirect->Link(), 301);
			} else {
				/** @var Renamed $renamed */
				$renamed = Renamed::get()->filter(array('OldLink' => $id));
				if ($renamed->count() > 0) {
					$this->redirect($renamed->First()->News()->Link(), 301);
				} else {
					$this->redirect($this->Link(), 404);
				}
			}
		} elseif ($action === 'latest') {
			/** @var News $item */
			$item = News::get()->filter(array('Live' => true))->first();
			$this->redirect($item->Link(), 302);
		}
	}

	/**
	 * Meta! This is so Meta! I mean, MetaTitle!
	 */
	public function MetaTitle()
	{
		$mapping = self::$url_handlers;
		if ($action = $this->getRequest()->param('Action')) {
			switch ($mapping[$action]) {
				case 'show' :
					$news = $this->getNews();
					if (isset($news)) {
						$this->Title = $news->Title . ' - ' . $this->Title;
					}
					break;
				case 'tag' :
					$tags = $this->getTag();
					if (isset($tags)) {
						$this->Title = $tags->Title . ' - ' . $this->Title;
					}
					break;
				case 'tags' :
					$this->Title = _t('News.ALLTAGS_PAGE', 'All tags - ') . $this->Title;
					break;
				case 'author' :
					$this->Title = _t('News.AUTHOR_PAGE', 'Items by ') . ucfirst($this->getRequest()->param('ID')) . ' - ' . $this->Title;
					break;
				case 'archive' :
					$this->Title = _t('News.ARCHIVE_PAGE', 'Items per period ') . $this->Title;
					break;
			}

			return $this->Title;
		}
	}

	/**
	 * I should make this configurable from SiteTree?
	 * Generate an RSS-feed.
	 * @todo obey translatable.
	 * @return RSSFeed $rss RSS-feed output.
	 */
	public function rss()
	{
		/** @var RSSFeed $rss */
		$rss = RSSFeed::create(
			$list = $this->getRSSFeed(), $link = $this->Link('rss'), $title = _t('News.RSSFEED', 'News feed')
		);

		return $rss->outputToBrowser();
	}

	/**
	 * @todo make language-specific versions
	 * @return DataList $return with Newsitems
	 */
	public function getRSSFeed()
	{
		$return = $this->NewsItems()
			->filter(array('Live' => 1))
			->exclude(array('PublishFrom:GreaterThan' => SS_Datetime::now()->Rfc2822()))
			->limit(10);

		return $return;
	}

	/**
	 * Setup the filter for the getters. This keeps in mind if the user is allowed to view this item.
	 * @param String $params returntype setting.
	 * @return Array $filter filter for general getter.
	 */
	private function setupFilter($params)
	{
		$filter = array(
			'URLSegment' => Convert::raw2sql($params['ID']),
			'Live'       => 1,
		);
		if (Member::currentUserID() !== 0 && !Permission::checkMember(Member::currentUserID(), array('VIEW_NEWS', 'CMS_ACCESS_NewsAdmin'))) {
			$filter['Live'] = 0;
		}

		return $filter;
	}

	/**
	 * @todo can this be made smaller? Would be nice!
	 * @return ArrayList $allEntries|$records The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews()
	{
		$siteConfig = $this->getCurrentSiteConfig();
		$exclude = array(
			'PublishFrom:GreaterThan' => SS_Datetime::now()->Format('Y-m-d'),
		);
		$filter = $this->generateAddedFilter();
		$allEntries = $this->Newsitems()
			->filter($filter)
			->exclude($exclude);
		/** Pagination pagination pagination. */
		if ($allEntries->count() > $siteConfig->PostsPerPage && $siteConfig->PostsPerPage > 0) {
			/** @var PaginatedList $records */
			$records = PaginatedList::create($allEntries, $this->getRequest());
			$records->setPageLength($siteConfig->PostsPerPage);

			return $records;
		}

		return $allEntries;
	}

	/**
	 * @todo Make this language-specific
	 * @return Tag All tags in a list.
	 */
	public function allTags()
	{
		return Tag::get();
	}

	/**
	 * Get the items, per month/year/author
	 * If no month or year is set, current month/year is assumed
	 * @todo cleanup the month-method maybe?
	 * @return Array $filter Filtering for the allNews getter
	 */
	public function generateAddedFilter()
	{
		$mapping = self::$url_handlers;
		$params = $this->getURLParams();
		/** @var array $filter Generic/default filter */
		$filter = array(
			'Live' => 1,
		);
		if (array_key_exists('Action', $params) && $params['Action'] !== null) {
			switch ($mapping[$params['Action']]) {
				/** Archive */
				case 'archive':
					if (!array_key_exists('ID', $params) || $params['ID'] === null) {
						$month = SS_DateTime::now()->Format('m');
						$year = SS_DateTime::now()->Format('Y');
					} elseif ((!array_key_exists('OtherID', $params) || $params['OtherID'] === null)
						&& (array_key_exists('ID', $params) && $params['ID'] !== null)) {
						$year = $params['ID'];
						$month = '';
					} else {
						$year = $params['ID'];
						$month = date_parse('01-' . $params['OtherID'] . '-1970');
						$month = str_pad((int)$month['month'], 2, "0", STR_PAD_LEFT);
					}
					$filter['PublishFrom:PartialMatch'] = $year . '-' . $month;
					break;
				/** Author */
				case 'author' :
					$filter['AuthorHelper.URLSegment:ExactMatch'] = $params['ID'];
					break;
			}
		}

		return $filter;
	}

	/**
	 * I'm tired of writing comments!
	 * @return form for Comments
	 */
	public function CommentForm()
	{
		$siteconfig = $this->getCurrentSiteConfig();
		$params = $this->getURLParams();

		return CommentForm::create($this, 'CommentForm', $siteconfig, $params);
	}

}
