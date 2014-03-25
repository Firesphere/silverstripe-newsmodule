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
		return $this->Newsitems()
			->filter(array('Live' => true))
			->exclude(array('PublishFrom:GreaterThan' => 'NOW()'));
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
	
	protected $current_item;
	
	protected $current_tag;

	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 */
	public function init() {
		parent::init();
		$this->needsRedirect();
		$this->setNews();
		// I would advice to put these in a combined file, but it works this way too.
		Requirements::javascript('silverstripe-newsmodule/javascript/jquery.tagcloud.js');
		Requirements::javascript('silverstripe-newsmodule/javascript/newsmodule.js');
	}

	/**
	 * Meta! This is so Meta! I mean, MetaTitle!
	 */
	public function MetaTitle(){
		$Params = $this->getURLParams();
		$news = $this->current_item;
		if($Params['Action'] == 'show' && $news->ID > 0){
			$this->Title = $news->Title . ' - ' . $this->Title;
		}
		elseif($Params['Action'] == 'tags'){
			$this->Title = 'All tags - ' . $this->Title;
		}
		elseif($Params['Action'] == 'tag'){
			$tags = $this->getTags();
			$this->Title = $tags->Title . ' - ' . $this->Title;
		}
	}
	
	/**
	 * Does this still work? I think it bugs.
	 * Or ignored, that could be it too.
	 */
	public function MetaDescription(){
		$Params = $this->getURLParams();
		/** @var News DataObject|DataObjectSet of Newsitems */
		$news = $this->current_item;
		if($Params['Action'] == 'show' && $news->ID > 0){
			$this->MetaDescription .= ' '.$news->Title;
		}
		elseif($Params['Action'] == 'tags'){
			$this->MetaDescription .= ' All tags';
		}
		elseif($tags = $this->getTags()){
			$this->MetaDescription .= ' ' . $tags->Title;
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
			$link = $this->Link("rss"),
			$title = "News feed"
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
	 * This feature is cleaner for redirection.
	 * Saves requests to the database if I'm not mistaken.
	 * @return redirect to either the correct page/object or do nothing (In that case, the item exists and we're gonna show it lateron).
	 */
	private function needsRedirect(){
		$Params = $this->getURLParams();
		if(isset($Params['Action']) && $Params['Action'] == 'show' && isset($Params['ID']) && is_numeric($Params['ID'])){
			if($Params['ID'] > 0){
				$redirect = $this->Newsitems()->filter('ID', $Params['ID'])->first();
				$this->redirect($redirect->Link(), 301);
			}
			else{
				$this->redirect($this->Link(), 404);
			}
		}
		else{
			$renamed = Renamed::get()->filter('OldLink', $Params['ID']);
			if($renamed->count() > 0){
				$this->redirect($renamed->First()->News()->Link(), 301);
			}
		}
	}
	
	/**
	 * General setter.
	 * We escape the tags here, otherwise things bug out with the meta-tags.
	 * @return News $news Current newsitem selected.
	 */
	public function setNews(){
		$Params = $this->getURLParams();
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'), 
		);
		/** @var array $segmentFilter Array containing the filter for current or any item */
		$segmentFilter = $this->setupFilter($Params);
		$news = $this->Newsitems()->filter($segmentFilter)->exclude($exclude)->first();
		$this->current_item = $news;
	}
	
	/**
	 * 
	 * @return News The current newsitem
	 */
	public function getNews() {
		return $this->current_item;
	}
	
	/**
	 * Check the user-permissions.
	 * @param String $Params returntype setting.
	 * @return Array $filter filter for general getter.
	 */
	private function setupFilter($Params){
		// Default filter.
		$filter = array(
			'URLSegment' => Convert::raw2sql($Params['ID']),
		);
		if(Member::currentUserID() != 0 && !Permission::checkMember(Member::currentUserID(), 'CMS_ACCESS_NewsAdmin')){
			$filter['Live'] = 1;
		}
		return $filter;
	}
	
	/**
	 * Get the correct tags.
	 * It would be kinda weird to get the incorrect tags, would it? Nevermind. Appearantly, it doesn't. Huh?
	 * @todo Implement translations?
	 * @todo this is somewhat unclean. One uses actual tags, the other a newsitem to get the tags.
	 * @param Boolean $news This is for the TaggedItems template. To only show the tags. Seemed logic to me.
	 * @return DataObject|DataList with tags or news.
	 */
	public function getTags($news = false){
		$Params = $this->getURLParams();
		$return = null;
		if(isset($Params['ID']) && $Params['ID'] != null){
			$tag = Tag::get()
				->filter(array('URLSegment' => Convert::raw2sql($Params['ID'])))
				->first();
			if(!$news){
				$return = $tag;
			}
			elseif($news && $tag->ID){
				/** Somehow, it really has to be an ArrayList of NewsItems. <% loop Tag.News %> doesn't work :( */
				$return = $tag->News()
					->filter(array('Live' => 1))
					->exclude(array('PublishFrom:GreaterThan' => date('Y-m-d H:i:s')));
			}				
			else{
				$this->redirect($this->Link('tags'), 404);
			}
		}
		else{
			$return = Tag::get();
		}
		return $return;
	}
		
	/** Redundant */
	public function currentTag(){
		return $this->getTags();
	}
	
	/**
	 * If we're on a newspage, we need to get the newsitem
	 * @return object of the item.
	 */
	public function currentNewsItem(){
		$siteConfig = SiteConfig::current_site_config();
		$newsItem = $this->current_item;
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
		$SiteConfig = SiteConfig::current_site_config();
		$Params = $this->getURLParams();
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'),
		);
		$filter = $this->generateAddedFilter($Params);
		$allEntries = $this->Newsitems()
			->filter($filter)
			->exclude($exclude);
		/** Pagination pagination pagination. */
		if($allEntries->count() > $SiteConfig->PostsPerPage && $SiteConfig->PostsPerPage > 0){
			$records = PaginatedList::create($allEntries,$this->request);
			$records->setPageLength($SiteConfig->PostsPerPage);
			return $records;
		}
		return $allEntries;
	}

	/**
	 * Get the items, per month/year/author
	 * If no month or year is set, current month/year is assumed
	 * @todo cleanup the month-method maybe?
	 * @param Array $Params URL parameters
	 * @return Array $filter Filtering for the allNews getter
	 */
	public function generateAddedFilter($Params){
		/** @var array $filter Generic/default filter */
		$filter = array(
			'Live' => 1, 
		);
		/** Archive */
		if($Params['Action'] == 'archive'){
			if(!isset($Params['ID'])){
				$month = date('m');
				$year = date('Y');
			}
			elseif(!isset($Params['OtherID']) && isset($Params['ID'])){
				$year = $Params['ID'];
				$month = '';
			}
			else{
				$year = $Params['ID'];
				$month = date_parse('01-'.$Params['OtherID'].'-1970');
				$month = $month['month'];
			}
			$filter['PublishFrom:PartialMatch'] = $year.'-'.$month;
		}
		/** Author */
		if($Params['Action'] == 'author'){
			$filter['AuthorHelper.URLSegment:ExactMatch'] = $Params['ID'];
		}
		return $filter;
	}
	
	/**
	 * I'm tired of writing comments!
	 * @return form for Comments
	 */
	public function CommentForm(){
		$siteconfig = SiteConfig::current_site_config();
		$params = $this->getURLParams();
		return(CommentForm::create($this, 'CommentForm', $siteconfig, $params));
	}
	
	/**
	 * This is to migrate existing newsitems to the new release with the new relational method.
	 * It is forward-non-destructive.
	 */
	public function migrate() {
		$newsitems = News::get();
		foreach($newsitems as $newsitem) {
			$newsitem->NewsHolderPages()->add($newsitem->NewsHolderPage());
		}
	}
}