<?php
/**
 * News page and controller.
 * 
 * NewsHolderPage
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 * @method News Newsitems() linked to this page (for Translatable)
 * @todo WHOAH! This thing is fat. Slim it down boy!
 * @todo besides the general getters, the news-functions should be in the model
 */
class NewsHolderPage extends Page {

	/** @var string $description PageType's description */
	private static $description = 'HolderPage for newsitems';
	
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
	 * @todo optional creation? I'm afraid here's a big potential bug at extending stuff!
	 */
	public function requireDefaultRecords()	{
		parent::requireDefaultRecords();
		if(NewsHolderPage::get()->count() == 0){
			$page = NewsHolderPage::create();
			$page->Title = _t('NewsHolderPage.DEFAULTPAGETITLE', 'Newspage');
			$page->Content = '';
			$page->URLSegment = 'news';
			$page->Sort = 1;
			$page->Status = 'Published';
			$page->write();
			$page->publish('Stage','Live');
			$page->flushCache();
			/** 
			 * This is to make sure we don't create any orphans by upgrading.
			 * It shouldn't be necessary, but we prefer to be safe over being sorry.
			 */
			$newsItems = News::get()->filter(array('NewsHolderPageID' => 0));
			if($newsItems->count()){
				foreach($newsItems as $newsItem){
					$newsItem->NewsHolderPageID = $this->ID;
					$newsItem->write();
				}
			}
			DB::alteration_message('Newsholder Page created', 'created');
		}
		/** @todo fix backward compatibility for Author-method */
		/** Backwards compatibility for upgraders. Update the PublishFrom field */
		$sql = "UPDATE `News` SET `PublishFrom` = `Created` WHERE `PublishFrom` IS NULL";
		DB::query($sql);
	}
	
	/**
	 * Support for children.
	 * Just call <% loop Children.Limit(x) %>$Title<% end_loop %> from your template to get the news-children.
	 * @return DataObjectSet NewsItems Items belonging to this page
	 */
	public function Children(){
		$now = date('Y-m-d');
		return $this->Newsitems()
			->filter(array('Live' => true))
			->exclude(array('PublishFrom:GreaterThan' => $now));
	}

}

class NewsHolderPage_Controller extends Page_Controller {

	/**
	 * We allow a lot, right?
	 * @var array $allowed_actions, again.
	 */
	private static $allowed_actions = array(
		'show',
		'tag',
		'tags',
		'rss',
		'archive',
		'author',
		'CommentForm',
		'migrate',
	);
	
	private static $url_handlers = array();
	
	protected $current_item;
	
	protected $current_tag;
	
	protected $current_siteconfig;
	
	/**
	 * Setup the allowed actions to work with the SiteConfig settings.
	 * @param type $limitToClass
	 * @return array
	 */
	public function allowedActions($limitToClass = null){
		$actions = parent::allowedActions($limitToClass);
		$defaultMapping = self::$allowed_actions;
		$siteConfig = $this->getCurrentSiteConfig();
		foreach($defaultMapping as $map) {
			$key = ucfirst($map.'Action');
			if($siteConfig->$key) {
				self::$allowed_actions[] = $siteConfig->$key;
			}
		}
		return array_merge($actions, self::$allowed_actions);
	}

	/**
	 * Setup the handling of the actions. This is needed for the custom URL Actions set in the SiteConfig
	 * @param SS_Request $request The given request
	 * @param string $action The requested action
	 * @return parent::handleAction
	 */
	public function handleAction($request, $action) {
		$handles = parent::allowedActions(false);
		$defaultMapping = self::$allowed_actions;
		$handles['index'] = 'handleIndex';
		$siteConfig = $this->getCurrentSiteConfig();
		foreach($defaultMapping as $key) {
			$map = ucfirst($key.'Action');
			if($siteConfig->$map) {
				$handles[$siteConfig->$map] = $key;
			}
			elseif(!isset($handles[$key])) {
				$handles[$key] = $key;
			}
		}
		self::$url_handlers = $handles;
		return parent::handleAction($request, $handles[$action]);
	}
	
	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 */
	public function init() {
		parent::init();
		$this->needsRedirect();
		// I would advice to put these in a combined file, but it works this way too.
		Requirements::javascript('silverstripe-newsmodule/javascript/jquery.tagcloud.js');
		Requirements::javascript('silverstripe-newsmodule/javascript/newsmodule.js');
	}

	/**
	 * Set the current newsitem, if available.
	 */
	public function setNews(){
		$Params = $this->getURLParams();
		/** @var array $segmentFilter Array containing the filter for current or any item */
		$segmentFilter = $this->setupFilter($Params);
		$news = $this->Newsitems()->filter($segmentFilter)->first();
		$this->current_item = $news;
	}
	
	/**
	 * Get the current newsitem
	 * @return News The current newsitem
	 */
	public function getNews() {
		if(!$this->current_item) {
			$this->setNews();
		}
		return $this->current_item;
	}
	
	/**
	 * Set the current tag
	 */
	public function setTag() {
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
	public function getTag(){
		if(!$this->current_tag){
			$this->setTag();
		}
		return $this->current_tag;
	}
	/**
	 * Set the current SiteConfig
	 */
	private function setCurrentSiteConfig() {
		$this->current_siteconfig = SiteConfig::current_site_config();
	}
	
	/**
	 * Get the current SiteConfig
	 * @return SiteConfig
	 */
	public function getCurrentSiteConfig() {
		if(!$this->current_siteconfig) {
			$this->setCurrentSiteConfig();
		}
		return $this->current_siteconfig;
	}

	/**
	 * This feature is cleaner for redirection.
	 * Saves requests to the database if I'm not mistaken.
	 * @return redirect to either the correct page/object or do nothing (In that case, the item exists and we're gonna show it lateron).
	 */
	private function needsRedirect(){
		$id = $this->getRequest()->param('ID');
		if($id && is_numeric($id)){
			if($id > 0){
				$redirect = $this->Newsitems()->byId($id);
				$this->redirect($redirect->Link(), 301);
			}
			else{
				$this->redirect($this->Link(), 404);
			}
		}
		else{
			$renamed = Renamed::get()->filter('OldLink', $id);
			if($renamed->count() > 0){
				$this->redirect($renamed->First()->News()->Link(), 301);
			}
		}
	}
	
	/**
	 * Meta! This is so Meta! I mean, MetaTitle!
	 */
	public function MetaTitle(){
		$mapping = self::$url_handlers;
		if($action = $this->getRequest()->param('Action')) {
			switch($mapping[$action]) {
				case 'show' :
					$news = $this->getNews();
					$this->Title = $news->Title . ' - ' . $this->Title;
					break;
				case 'tag' :
					$tags = $this->current_tag;
					$this->Title = $tags->Title . ' - ' . $this->Title;
					break;
				case 'tags' :
					$this->Title = _t('News.ALLTAGS_PAGE', 'All tags - ') . $this->Title;
					break;
				case 'author' :
					$this->Title = _t('News.AUTHOR_PAGE', 'Items by author - ') . $this->Title;
					break;
				case 'archive' :
					$this->Title = _t('News.ARCHIVE_PAGE', 'Items per period ') . $this->Title;
					break;
			}
		}
	}
	
	/**
	 * I should make this configurable from SiteTree?
	 * Generate an RSS-feed.
	 * @todo obey translatable.
	 * @return RSSFeed $rss RSS-feed output.
	 */
	public function rss(){ 
		$rss = RSSFeed::create(
			$list = $this->getRSSFeed(),
			$link = $this->Link('rss'),
			$title = _t('News.RSSFEED', 'News feed')
		);
		return $rss->outputToBrowser();
	}

	/**
	 * @todo make language-specific versions
	 * @return DataList $return with Newsitems
	 */
	public function getRSSFeed() {
		$return = $this->NewsItems()->filter(
				array('Live' => 1)
			)->exclude(
				array('PublishFrom:GreaterThan' => date('Y-m-d H:i:s'))
			)->limit(10);
		return $return;
	}
	
	/**
	 * Setup the filter for the getters. This keeps in mind if the user is allowed to view this item.
	 * @param String $Params returntype setting.
	 * @return Array $filter filter for general getter.
	 */
	private function setupFilter($Params){
		// Default filter.
		$filter = array(
			'URLSegment' => Convert::raw2sql($Params['ID']),
		);
		if(Member::currentUserID() != 0 && !Permission::checkMember(Member::currentUserID(), array('VIEW_NEWS', 'CMS_ACCESS_NewsAdmin'))){
			$filter['Live'] = 1;
		}
		return $filter;
	}

	/**
	 * If we're on a newspage, we need to get the newsitem
	 * @return object of the item.
	 */
	public function currentNewsItem(){
		$siteConfig = $this->getCurrentSiteConfig();
		$newsItem = $this->getNews();
		if ($newsItem) {
			/** If either one of these is false, no comments are allowed */
			$newsItem->AllowComments = ($siteConfig->Comments && $newsItem->Commenting);
			return($newsItem);
		}
		return array(); /** Return an empty page. Somehow the visitor ended up here, so at least give him something */
	}

	/**
	 * @todo can this be made smaller? Would be nice!
	 * @return ArrayList $allEntries|$records The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		$siteConfig = $this->getCurrentSiteConfig();
		$Params = $this->getURLParams();
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'),
		);
		$filter = $this->generateAddedFilter($Params);
		$allEntries = $this->Newsitems()
			->filter($filter)
			->exclude($exclude);
		/** Pagination pagination pagination. */
		if($allEntries->count() > $siteConfig->PostsPerPage && $siteConfig->PostsPerPage > 0){
			$records = PaginatedList::create($allEntries,$this->getRequest());
			$records->setPageLength($siteConfig->PostsPerPage);
			return $records;
		}
		return $allEntries;
	}
	
	/**
	 * @todo Make this language-specific
	 * @return Tag All tags in a list.
	 */
	public function allTags() {
		return Tag::get();
	}

	/**
	 * Get the items, per month/year/author
	 * If no month or year is set, current month/year is assumed
	 * @todo cleanup the month-method maybe?
	 * @param Array $params URL parameters
	 * @return Array $filter Filtering for the allNews getter
	 */
	public function generateAddedFilter($params){
		/** @var array $filter Generic/default filter */
		$filter = array(
			'Live' => 1, 
		);
		/** Archive */
		if($params['Action'] == 'archive'){
			if(!isset($params['ID'])){
				$month = date('m');
				$year = date('Y');
			}
			elseif(!isset($params['OtherID']) && isset($params['ID'])){
				$year = $params['ID'];
				$month = '';
			}
			else{
				$year = $params['ID'];
				$month = date_parse('01-'.$params['OtherID'].'-1970');
				$month = $month['month'];
			}
			$filter['PublishFrom:PartialMatch'] = $year.'-'.$month;
		}
		/** Author */
		if($params['Action'] == 'author'){
			$filter['AuthorHelper.URLSegment:ExactMatch'] = $params['ID'];
		}
		return $filter;
	}
	
	/**
	 * I'm tired of writing comments!
	 * @return form for Comments
	 */
	public function CommentForm(){
		$siteconfig = $this->getCurrentSiteConfig();
		$params = $this->getURLParams();
		return(CommentForm::create($this, 'CommentForm', $siteconfig, $params));
	}
	
	/**
	 * This is to migrate existing newsitems to the new release with the new relational method.
	 * It is forward-non-destructive.
	 * @todo this Migration is broken because the @method NewsHolderPage NewsHolderPage() doesn't exist on News anymore.
	 */
	public function migrate() {
		$newsitems = News::get();
		foreach($newsitems as $newsitem) {
			$newsitem->NewsHolderPages()->add($newsitem->NewsHolderPage());
		}
	}
}