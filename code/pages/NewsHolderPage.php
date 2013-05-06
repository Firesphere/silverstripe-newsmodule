<?php
/**
 * News page and controller, not really complicated yet :) 
 * @todo fix the double-allowed.
 * @package News/blog module
 * @author Simon 'Sphere'
 * @todo refactor refactor refactor
 * @todo port things to the News itself instead of handling here
 * @todo Clean this up
 * @todo Move the news functions as extensions to a separate extension
 * @todo Move the tags functions as extensions to a separate extension
 * @todo Uncluttering
 * @method Newsitems Objects of News
 */
class NewsHolderPage extends Page {

   
	public static $has_many = array(
		'Newsitems' => 'News',
	);

	/**
	 * This one bugs out :( on live
	 * @param type $includeTitle boolean
	 * @return type string of meta-tags
	 * @todo fix the darn thing.
	 */
	public function MetaTags($includeTitle = true) {
		if( Controller::curr() instanceof NewsHolderPage_Controller && ($record = Controller::curr()->getNews())) {
			return $record->MetaTags($includeTitle);
		}
		return parent::MetaTags($includeTitle);
	}
	
	/**
	 * The following three functions are global once enabled!
	 * @todo Can I maybe implement this somewhere less annoying?
	 * @param type $arguments from Content.
	 * @return HTML block with the parsed code.
	 */
	public static function TweetHandler($arguments) {
		if(!isset($arguments['id'])){
			return null;
		}
		if(substr($arguments['id'], 0, 4) == 'http'){
			list($unneeded,$id) = explode('/status/', $arguments['id']);
		}
		else{
			$id = $arguments['id'];
		}
		$data = json_decode(file_get_contents('https://api.twitter.com/1/statuses/oembed.json?id='.$id.'&omit_script=true&lang=en'), 1);
		return ($data['html']);
	}
	
	/**
	 * @param string $arguments array with the type
	 * @param type $code string of the code to parse 
	 * @return type HTMLString of parsed code.
	 */
	public static function GeshiParser($arguments, $code){
		if(!isset($arguments['type'])){
			// Assuming most code is PHP.
			$arguments['type'] = 'php';
		}
		$geshi = new GeSHi(html_entity_decode(str_replace('<br>', "\n", $code)), $arguments['type']);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		return $geshi->parse_code();
	}
	
	/**
	 * @param type $arguments array of arguments from the content
	 * @param type $caption text between the [] [/] brackets
	 * @return type HTMLString of parsed youtube movie.
	 */
	public static function YouTubeHandler($arguments,$caption = null) {
		// If there's no ID, just stop.
		if (empty($arguments['id'])) {
			return;
		}
		/*** SET DEFAULTS ***/
		$defaults = array(
			'YouTubeID' => $arguments['id'],
			'autoplay' => false,
			'caption' => $caption ? Convert::raw2xml($caption) : false,
			'width' => 640,
			'height' => 385,
		);

		//overide the defaults with the arguments supplied
		$customise = array_merge($defaults,$arguments);
		$template = new SSViewer('YouTube');
		return $template->process(new ArrayData($customise));
	}

	/**
	 * @param type $arguments null
	 * @return type HTML Parsed for template.
	 */
	public static function createSlideshow($arguments){
		if( Controller::curr() instanceof NewsHolderPage_Controller && ($record = Controller::curr()->getNews())) {
			$SiteConfig = SiteConfig::current_site_config();
			if($SiteConfig->SlideshowInitial){
				$template = 'NewsSlideShowFirst';
			}
			else{
				$template = 'NewsSlideShowAll';
			}
			$record->Image = $record->SlideshowImages()->sort('SortOrder ASC');
			$template = new SSViewer($template);
			return($template->process($record));
		}
	}
	
	/**
	 * Create a default NewsHolderPage. This prevents error500 because of a missing page.
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
			DB::alteration_message('Newsholder Page created', 'created');
		}
	}
	
}

class NewsHolderPage_Controller extends Page_Controller {

	public static $allowed_actions = array(
		'allNews',
		'show',
		'tag',
		'tags',
		'rss',
		'CommentForm',
		'CommentStore',
	);

	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 * Annoying date features. Override! 
	 */
	public function init() {
		parent::init();
		/**
		 * Note, these need to be initialized before newsmodule.js!
		 * Fix coming up, this is Development after all :)
		 */
		if(Member::currentUserID() > 0 && Permission::checkMember(Member::currentUserID(), 'CMSACCESSNewsAdmin')){
			Requirements::css('silverstripe-newsmodule/javascript/thirdparty/Aloha/css/aloha.css');
			Requirements::javascript('silverstripe-newsmodule/javascript/thirdparty/Aloha/lib/require.js');
			Requirements::javascript('silverstripe-newsmodule/javascript/thirdparty/Aloha/lib/aloha-full.min.js');
		}
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
		elseif($tags = $this->getTags() && $Params['Action'] == 'tag'){
			$this->Title = $tags->Title . ' - ' . $this->Title;
		}
	}
	
	/**
	 * Does this still work? I think it bugs.
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
	 * @todo obey translatable, but how?
	 * @return type RSS-feed output.
	 */
	public function rss(){ 
		$rss = new RSSFeed(
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
	 * General getter. Should this even be public?
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
		elseif($Params['Action'] == 'archive'){
			// Archive if wished.
			$config = SiteConfig::current_site_config();
			$date = date('Y-m-d', strtotime(date('Y-m-d') . ' -' . $config->AutoArchiveDays . ' days'));
			return News::get()->filter(
				array(
					'NewsHolderPageID' => $this->ID,
					'Live' => 1,
					'Created:LessThan' => $date
				)
			);
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
		if(Member::currentUserID() != 0 && !Permission::checkMember(Member::currentUserID(), 'CMSACCESSNewsAdmin')){
			$idFilter = array(
				'ID' => $Params['ID'],
				'Live' => 1
			);
			$segmentFilter = array(
				'URLSegment' => $Params['ID'],
				'Live' => 1
			);
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
	 * Just return this. currentNewsItem should fix it. This one is for show.
	 * @todo fix redundancy check. It makes 5 requests to the database. It should be 1, preferably.
	 * @return object this. Forreal! Or, redirect if getNews() returns false.
	 */
	public function show() {
		$Params = $this->getURLParams();
		if($Params['ID'] != null){
			return $this;
		}
		else{
			$this->redirect($this->Link());
		}
	}
	/**
	 * Handle the Archive, if needed.
	 * @return \NewsHolderPage_Controller
	 */
	public function archive() {
		return $this;
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
	
	/**
	 * Tag functions. They always return this, so the template addressing can address the getTags function.
	 * I wonder if it really needs to do the check, it's another redundancy-thing.
	 * @todo Fix the redundant check. It's all extra operations now.
	 * @return \NewsHolderPage_Controller
	 */
	public function tag(){
		$Params = $this->getURLParams();
		if($Params['ID'] != null){
			return $this;
		}
		else{
			$this->redirect($this->Link('tags'));
		}
	}
	
	public function tags(){
		return $this;
	}

	public function currentTag(){
		return $this->getTags();
	}

	/**
	 * @todo fix translation support here.
	 * @return object The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		$SiteConfig = SiteConfig::current_site_config();
		if(!$SiteConfig->AutoArchive || $SiteConfig->AutoArchiveDays == 0){
			$filter = array(
				'Live' => 1, 
				'NewsHolderPageID' => $this->ID
			);
			$allEntries = News::get()
				->filter($filter)
				->where('PublishFrom IS NULL OR PublishFrom <= \'' . date('Y-m-d') . '\'');
		}
		else{
			/**
			 * If archived, get the non-archived items.
			 * This should work without a hitch!
			 */
			$filter = array(
				'Created:GreaterThan' => date('Y-m-d', strtotime(date('Y-m-d').' -'.$SiteConfig->AutoArchiveDays.' days')),
				'Live' => 1,
				'NewsHolderPageID' => $this->ID
			);
			$allEntries = News::get()
				->filter($filter)
				->where('PublishFrom IS NULL OR PublishFrom <= \'' . date('Y-m-d') . '\'');
		}
		/**
		 * Pagination pagination pagination.
		 */
		if($allEntries->count() > 0){
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
		return false;
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
		 */
		if(!isset($data['Extra']) || $data['Extra'] == ''){
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
