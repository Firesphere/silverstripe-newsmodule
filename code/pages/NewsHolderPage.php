<?php
/**
 * News page and controller.
 * 
 * NewsHolderPage
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 * @method Newsitems News Newsitems linked to this page (for Translatable)
 * @todo WHOAH! This thing is fat. Slim it down boy!
 * @todo besides the general getters, the news-functions should be in the model
 */
class NewsHolderPage extends Page {

	/** @var string $description PageType's description */
	private static $description = 'HolderPage for newsitems';

	/** @var array $has_many many-to-one relationships */
	private static $has_many = array(
		'Newsitems' => 'News',
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
			$page->Title = _t($this->class . '.DEFAULTPAGETITLE', 'Newspage');
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
			$newsItems = News::get();
			foreach($newsItems as $newsItem){
				if($newsItem->NewsHolderPageID == 0){
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
		/** If the Translatable is added lateron, update the locale to at least have some value */
		if(class_exists('Translatable')){
			$sqlLang = "UPDATE `News` SET `Locale` = '".Translatable::get_current_locale()."' WHERE Locale IS NULL";
			DB::query($sqlLang);
		}
	}
	
	/**
	 * Support for children.
	 * Just call <% loop Children.Limit(x) %>$Title<% end_loop %> from your template to get the news-children.
	 * Isn't this supposed to be handled in the allowed_children?
	 * Anyway. If you don't like children... Rename this.
	 * @return DataObjectSet NewsItems Items belonging to this page
	 */
	public function Children(){
		return $this->Newsitems();
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
	);

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
	 * Meta! This is so Meta! I mean, MetaTitle!
	 */
	public function MetaTitle(){
		$Params = $this->getURLParams();
		$news = $this->getNews();
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
		$news = $this->getNews();
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
		$return = $this->NewsItems()
			->filter(
				array('Live' => 1)
			)
			->exclude(
				array('PublishFrom:GreaterThan' => date('Y-m-d H:i:s'))
			)
			->limit(10);
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
	 * General getter. Should this even be public? (yes, it needs to be public)
	 * We escape the tags here, otherwise things bug out with the meta-tags.
	 * @return News $news Current newsitem selected.
	 */
	public function getNews(){
		$Params = $this->getURLParams();
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'), 
		);
		/** @var array $segmentFilter Array containing the filter for current or any item */
		$segmentFilter = $this->setupFilter($Params);
		$news = $this->Newsitems()->filter($segmentFilter)->exclude($exclude)->first();
		return $news;
	}
	
	/**
	 * Check the user-permissions.
	 * @param String $type returntype setting.
	 * @return Array $filter filter for general getter.
	 */
	private function setupFilter($Params){
		// Default filter.
		$filter = array(
			'NewsHolderPageID' => $this->ID,
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
				->filter(
					array('URLSegment' => Convert::raw2sql($Params['ID']))
				)
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
	
	/**
	 * If we're on a newspage, we need to get the newsitem
	 * @return object of the item.
	 */
	public function currentNewsItem(){
		$siteConfig = SiteConfig::current_site_config();
		$newsItem = $this->getNews();
		if ($newsItem) {
			/** If either one of these is false, no comments are allowed */
			$newsItem->AllowComments = ($siteConfig->Comments && $newsItem->Commenting);
			return($newsItem);
		}
		return array(); /** Return an empty page. Somehow the visitor ended up here, so at least give him something */
	}
	
	/** Redundant */
	public function currentTag(){
		return $this->getTags();
	}

	/**
	 * @todo can this be made smaller? Would be nice!
	 * @return object The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		$SiteConfig = SiteConfig::current_site_config();
		$Params = $this->getURLParams();
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'),
		);
		$filter = $this->generateAddedFilter($Params);
		if(!$filter){
			$this->Redirect($this->Link(), 404);
		}
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
			'NewsHolderPageID' => $this->ID,
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
			}
			$filter['PublishFrom:PartialMatch'] = $year.'-'.$month['month'];
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
}