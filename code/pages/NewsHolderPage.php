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
		'allNews',
		'show',
		'tag',
		'tags',
		'rss',
		'archive',
		'CommentForm',
		'CommentStore',
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
			->filter(array('Live' => 1))
			->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
			->sort('IF(PublishFrom, PublishFrom, Created)', "DESC")
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
			if(isset($Params['ID']) && is_numeric($Params['ID'])){
				$redirect = News::get()->filter('ID', $Params['ID'])->first();
				if($redirect->ID > 0){
					$this->redirect($redirect->Link(), 301);
				}
				else{
					$this->redirect($this->Link(), 404);
				}
			}
			else{
				$news = News::get()->filter(
					array(
						'URLSegment' => $Params['ID'],
						'NewsHolderPageID' => $this->ID
					)
				);
				if($news->count() == 0){
					$renamed = Renamed::get()->filter('OldLink', $Params['ID']);
					if($renamed->count() > 0){
						$this->redirect($renamed->First()->News()->Link(), 301);
					}
					else{
						$this->redirect($this->Link(), 404);
					}
				}
			}
		}
	}
	
	/**
	 * General getter. Should this even be public? (yes, it needs to be public)
	 * We escape the tags here, otherwise things bug out with the meta-tags.
	 * @todo clean up more. Still unhappy with this mess.
	 * @return boolean or object. If object, we are successfully on a page. If boolean, it's baaaad.
	 */
	public function getNews(){
		$Params = $this->getURLParams();
		// Default filter.
		$filter = array(
			'NewsHolderPageID' => $this->ID,
		);
		// Filter based on login-status.
		$idFilter = $this->checkPermission('id');
		$segmentFilter = $this->checkPermission('segment');
		// Skip if we're not on show or archive.
		if($Params['Action'] == 'show'){
			// Redirect if the ID is numeric
			if(is_numeric($Params['ID'])){
				$filter = array_merge($idFilter,$filter);
				$news = News::get()->filter($filter)->first();
				$link = $this->Link('show/').$news->URLSegment;
				$this->redirect($link, 301);
			}
			else{
				// get the news.
				$filter = array_merge($segmentFilter,$filter);
				$news = News::get()->filter($filter)
					->where('PublishFrom IS NULL OR PublishFrom <= \'' . date('Y-m-d') . '\'');
				if($news->count() > 0){
					$news = $news->first();
					return $news;
				}
				else{
					$renamed = Renamed::get()->filter(array('OldLink' => $Params['ID']));
					if($renamed->count() > 0){
						$link = ($renamed->first()->News()->Link());
						$this->redirect($link, 301);
					}
				}
			}
			return false;
		}
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
		$idFilter = array(
			'ID' => $Params['ID']
		);
		$segmentFilter = array(
			'URLSegment' => $Params['ID']
		);
		if(Member::currentUserID() != 0 && !Permission::checkMember(Member::currentUserID(), 'CMS_ACCESS_NewsAdmin')){
			$idFilter['Live'] = 1;
			$segmentFilter['Live'] = 1;
		}
		if($type == 'id'){
			return $idFilter;
		}
		elseif($type == 'segment'){
			return $segmentFilter;
		}
	}
	
	/**
	 * Get the correct tags.
	 * It would be kinda weird to get the incorrect tags, would it? Nevermind. Appearantly, it doesn't. Huh?
	 * @todo Implement translations?
	 * @param type $news This is for the TaggedItems template. To only show the tags. Seemed logic to me.
	 * @return type DataObject or DataList with tags or news.
	 */
	public function getTags($news = false){
		$Params = $this->getURLParams();
		if(isset($Params['ID']) && $Params['ID'] != null){
			$tagItems = Tag::get()->filter(array('URLSegment' => $Params['ID']))->first();
			if($tagItems->News()->count() > 0 && !$news){
				$return = $tagItems;
			}
			elseif($tagItems->News()->count() > 0 && $news){
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $tagItems->ID)
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= \'' . date('Y-m-d') . '\'');
				$return = $news;
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
			$newsItem->AllowComments = $siteConfig->Comments;
			return($newsItem);
		}
	}
	
	public function currentTag(){
		return $this->getTags();
	}

	/**
	 * @return object The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		$SiteConfig = SiteConfig::current_site_config();
		$filter = array(
			'Live' => 1, 
			'NewsHolderPageID' => $this->ID
		);
		$allEntries = News::get()
			->filter($filter)
			->where('PublishFrom IS NULL OR PublishFrom <= \'' . date('Y-m-d') . '\'');
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
	public function getArchive(){
		$SiteConfig = SiteConfig::current_site_config();
		$Params = $this->getURLParams();
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
			$month = $Params['OtherID'];
		}
		/**
		 * This needs cleanup.
		 */
		$allEntries = News::get()
			->filter(
				array(
					'Live' => 1, 
					'NewsHolderPageID' => $this->ID,
					'Created:PartialMatch' => $year.'-'.$month
				)
			)
			->where('PublishFrom LIKE \''.$year.'-'.$month.'%\' OR PublishFrom IS NULL');
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
	 * I'm tired of writing comments!
	 * Ok, well, here, I build a form. Nice, huh?
	 * @return form for Comments
	 */
	public function CommentForm(){
		// I don't know if this is needed, I think it's handled in the template already.
		// But it's not bad for safety.
		$siteconfig = SiteConfig::current_site_config();
		/**
		 * Are comments allowed? 
		 */
		if(!$siteconfig->Comments){
			return false;
		}
		$params = $this->getURLParams();
		$return = 'CommentForm';
		$field = array();

		/**
		 * Include the ID of the current item. Otherwise we can't link correctly. 
		 */
		$NewsID = $this->request->postVar('NewsID');
		if($NewsID == null){
			$newsItem = News::get()->filter(array('URLSegment' => $params['ID']))->first();
			$field[] = HiddenField::create('NewsID', '', $newsItem->ID);
		}
		$field[] = TextField::create('Name', _t($this->class . '.COMMENT.NAME', 'Name'));
		$field[] = TextField::create('Title', _t($this->class . '.COMMENT.TITLE', 'Comment title'));
		$field[] = TextField::create('Email', _t($this->class . '.COMMENT.EMAIL', 'E-mail'));
		$field[] = TextField::create('URL', _t($this->class . '.COMMENT.WEBSITE', 'Website'));
		$field[] = TextareaField::create('Comment', _t($this->class . '.COMMENT.COMMENT', 'Comment'));
		/**
		 * See the README.md about this!
		 */
		if($siteconfig->ExtraSecurity){
			$field[] = TextField::create('Extra', _t($this->class . '.COMMENT.EXTRA', 'Extra'));
		}
		if($siteconfig->NoscriptSecurity){
			$field[] = LiteralField::create('noscript', '<noscript><input type="hidden" value="1" name="nsas" /></noscript>');
		}
		$fields = FieldList::create(
			$field
		);
		
		 $actions = FieldList::create(
			FormAction::create('CommentStore', 'Send')
		);
		$required_fields = array(
			'Name',
			'Title',
			'Email',
			'Comment'
		); 
		$validator = RequiredFields::create($required_fields);

		return(Form::create($this, $return, $fields, $actions, $validator));
	}
	
	/**
	 * Store it.
	 * And also check if it's no double-post. Limited to 60 seconds, but it can be differed.
	 * I wonder if this is XSS safe? The saveInto does this for me, right?
	 * @param array $data
	 * @param object $form 
	 */
	public function CommentStore($data, $form){
		/**
		 * If the "Extra" field is filled, we have a bot.
		 * Also, the nsas (<noscript> Anti Spam) is a bot. Bot's don't use javascript.
		 * Note, a legitimate visitor that has JS disabled, will be unable to post!
		 */
		if(!isset($data['Extra']) || $data['Extra'] == '' || isset($data['nsas'])){
			$data['Comment'] = Convert::raw2sql($data['Comment']);
			if(!Comment::get()->where('Comment LIKE \'' . $data['Comment'] . '\' AND ABS(TIMEDIFF(NOW(), Created)) < 60')->count()){
				$comment = new Comment();
				$form->saveInto($comment);
				$comment->NewsID = $data['NewsID'];
				$comment->write();
			}
		}
		$this->redirectBack();
	}
}
