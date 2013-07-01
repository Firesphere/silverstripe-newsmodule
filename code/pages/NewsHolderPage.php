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
 */
class NewsHolderPage extends Page {

	private static $description = 'HolderPage for newsitems';
   
	private static $has_many = array(
		'Newsitems' => 'News',
	);
	
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
	 */
	public function Children(){
		return $this->Newsitems();
	}

}

class NewsHolderPage_Controller extends Page_Controller {

	/**
	 * We allow a lot, right?
	 * @var array, again.
	 */
	private static $allowed_actions = array(
		'show',
		'tag',
		'tags',
		'rss',
		'archive',
		'CommentForm',
	);

	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 * Annoying date features. Override! 
	 */
	public function init() {
		parent::init();
		$this->needsRedirect();
		// I would advice to put these in a combined file, but it works this way too.
		Requirements::javascript('silverstripe-newsmodule/javascript/jquery.tagcloud.js');
		Requirements::javascript('silverstripe-newsmodule/javascript/newsmodule.js');
		setlocale(LC_ALL, i18n::get_locale());
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
	 * @return type RSS-feed output.
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
	 * @return type DataList with Newsitems
	 */
	public function getRSSFeed() {
		$return = News::get()
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
	 * @todo Find a cleaner way to do this. This is ugly.
	 * @return redirect to either the correct page/object or do nothing (In that case, the item exists and we're gonna show it lateron).
	 */
	private function needsRedirect(){
		$Params = $this->getURLParams();
		if(isset($Params['Action']) && $Params['Action'] == 'show' && isset($Params['ID'])){
			if(is_numeric($Params['ID'])){
				$redirect = News::get()->filter('ID', $Params['ID'])->first();
				if($redirect->ID > 0){
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
	}
	
	/**
	 * General getter. Should this even be public? (yes, it needs to be public)
	 * We escape the tags here, otherwise things bug out with the meta-tags.
	 * @return object of Newsitem.
	 */
	public function getNews(){
		$Params = $this->getURLParams();
		// Default filter.
		$filter = array(
			'NewsHolderPageID' => $this->ID,
		);
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'), 
		);
		// Filter based on login-status.
		$segmentFilter = $this->checkPermission('segment');
		// Skip if we're not on show.
		if($Params['Action'] == 'show'){
			$filter = array_merge($segmentFilter,$filter);
			$news = News::get()->filter($filter)->exclude($exclude)->first();
			return $news;
		}
		return array();
	}
	
	/**
	 * Check the user-permissions.
	 * @param type $type string with returntype setting.
	 * @return type $filter array for the filter.
	 */
	private function checkPermission($type){
		$Params = $this->getURLParams();
		/**
		 * Let the member, if he has access to the NewsAdmin, preview the post even if it's not published yet.
		 */
		$segmentFilter = array(
			'URLSegment' => Convert::raw2sql($Params['ID']),
		);
		if(Member::currentUserID() != 0 && !Permission::checkMember(Member::currentUserID(), 'CMS_ACCESS_NewsAdmin')){
			$segmentFilter['Live'] = 1;
		}
		return $segmentFilter;
	}
	
	/**
	 * Get the correct tags.
	 * It would be kinda weird to get the incorrect tags, would it? Nevermind. Appearantly, it doesn't. Huh?
	 * @todo Implement translations?
	 * @todo this is somewhat unclean. One uses actual tags, the other a newsitem to get the tags.
	 * @param type $news This is for the TaggedItems template. To only show the tags. Seemed logic to me.
	 * @return type DataObject or DataList with tags or news.
	 */
	public function getTags($news = false){
		$Params = $this->getURLParams();
		if(isset($Params['ID']) && $Params['ID'] != null){
			$tag = Tag::get()
				->filter(
					array('URLSegment' => Convert::raw2sql($Params['ID']))
				)
				->first();
			if(!$news){
				$return = $tag;
			}
			elseif($tag->News()->count() > 0 && $news){
				/** Somehow, it really has to be an ArrayList of NewsItems. <% loop Tag.News %> doesn't work :( */
				$return = $tag->News()
					->filter(array('Live' => 1)
					)
					->exclude(array('PublishFrom:GreaterThan' => date('Y-m-d H:i:s')));
			}				
			else{
				$this->redirect('tag');
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
		$filter = array(
			'Live' => 1, 
			'NewsHolderPageID' => $this->ID,
		);
		$exclude = array(
			'PublishFrom:GreaterThan' => date('Y-m-d H:i:s'),
		);
		if(isset($Params['Action']) && $Params['Action'] == 'archive'){
			$filter = array_merge($filter, $this->generateArchiveFilter($Params));
		}
		$allEntries = News::get()
			->filter($filter)
			->exclude($exclude);
		/**
		 * Pagination pagination pagination.
		 */
		if($allEntries->count() > $SiteConfig->PostsPerPage){
			$records = PaginatedList::create($allEntries,$this->request);
			if($SiteConfig->PostsPerPage == 0){
				$records->setPageStart(1);
				$records->setLimititems(0);
			}
			else{
				$records->setPageLength($SiteConfig->PostsPerPage);
			}
			return $records;
		}
		return $allEntries;
	}
	
	/**
	 * Get the items, per month/year
	 * If no month or year is set, current month/year is assumed
	 * @todo sidebar with year/month grouping.
	 */
	public function generateArchiveFilter($Params){
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
		$archivefilter = array(
			'PublishFrom:PartialMatch' => $year.'-'.$month['month']
		);
		return $archivefilter;
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
