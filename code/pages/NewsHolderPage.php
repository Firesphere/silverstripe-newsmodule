<?php
/**
 * News page and controller, not really complicated yet :) 
 * 
 * @package News/blog module
 * @author Simon 'Sphere' 
 */
class NewsHolderPage extends Page {

   
	public static $has_many = array(
		'Newsitems' => 'News',
	);
	
	public static $icon = 'newsadmin/images/icons/news';

	/**
	 * This one bugs out :( on live
	 * @param type $includeTitle boolean
	 * @return type string of meta-tags
	 */
	public function MetaTags($includeTitle = true) {
		if( Controller::curr() instanceof NewsHolderPage_Controller && ($record = Controller::curr()->getNews())) {
			//Someone please tell me I didn't forget the actual frikkin' MetaTags function?
			return $record->MetaTags($includeTitle);
			// Crap. Ok, working on it!
			// Note, this requires the OpenGraph Module!
		}
		return parent::MetaTags($includeTitle);
	}
	
	/**
	 * The following three functions are global once enabled!
	 * @param type $arguments from Content.
	 * @return HTML block with the parsed code.
	 */
	public static function TweetHandler($arguments) {
		if(!isset($arguments['id'])){
			return null;
		}
		if(substr($arguments['id'], 0, 4) == 'http'){
			$id = explode('/status/', $arguments['id']);
			$id = $id[1];
		}
		else{
			$id = $arguments['id'];
		}
		$data = json_decode(file_get_contents('https://api.twitter.com/1/statuses/oembed.json?id='.$id.'&omit_script=true&lang=en'), 1);
		return ($data['html']);
	}
	
	public static function GeshiParser($arguments, $caption){
		if(!isset($arguments['type'])){
			$arguments['type'] = 'php';
		}
		$geshi = new GeSHi(html_entity_decode(str_replace('<br>', "\n", $caption)), $arguments['type']);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		return $geshi->parse_code();
	}
	
	public static function YouTubeHandler($arguments,$caption = null,$parser = null) {
		// first things first, if we dont have a video ID, then we don't need to
		// go any further
		if (empty($arguments['id'])) {
			return;
		}

		$customise = array();
		/*** SET DEFAULTS ***/
		$customise['YouTubeID'] = $arguments['id'];
		//play the video on page load
		$customise['autoplay'] = false;
		//set the caption
		$customise['caption'] = $caption ? Convert::raw2xml($caption) : false;
		//set dimensions
		$customise['width'] = 640;
		$customise['height'] = 385;

		//overide the defaults with the arguments supplied
		$customise = array_merge($customise,$arguments);

		//get our YouTube template
		$template = new SSViewer('YouTube');

		//return the customised template
		return $template->process(new ArrayData($customise));
	}

}

/**
 * Although... 
 */
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
	 * Meta! This is so Meta!
	 * Yes, Meta stuff here :)
	 * Either for tags or news. I'm not entirely sure about the params check, this might be the wrong way around.
	 * All just setting, no returning needed since it's $this.
	 */
	public function MetaTitle(){
		$Params = $this->getURLParams();
		if($news = $this->getNews()){      
			$this->Title = $news->Title . ' - ' . $this->Title;
		}
		elseif($Params['Action'] == 'tags'){
			$this->Title = 'All tags - ' . $this->Title;
		}
		elseif($tags = $this->getTags()){
			$this->Title = $tags->Title . ' - ' . $this->Title;
		}
	}
	
	public function MetaKeywords(){
		if($news = $this->getNews()){      
			$this->MetaKeywords .= implode(', ', explode(' ', $news->Title));
		}		
		elseif($Params['Action'] == 'tags'){
			$this->MetaKeywords .= ', All, tags';
		}
		elseif($tags = $this->getTags()){
			$this->MetaKeywords .= $tags->Title;
		}
	}
	
	public function MetaDescription(){
		if($news = $this->getNews()){      
			$this->MetaDescription .= ' '.$news->Title;
		}
		elseif($Params['Action'] == 'tags'){
			$this->MetaDescription .= ' All tags';
		}
		elseif($tags = $this->getTags()){
			$this->MetaDescription .= ' ' . $tags->Title;
		}
	}
	
	public function MetaTags(){
		return $this->MetaTags();
	}

	/**
	 * Include the tagcloud scripts. Configure in newsmodule.js!
	 * Annoying date features. Override! 
	 */
	public function init() {
		parent::init();
		// I would advice to put these in a combined file, but it works this way too.
		Requirements::javascript('silverstripe-newsmodule/javascript/jquery.tagcloud.js');
		Requirements::javascript('silverstripe-newsmodule/javascript/newsmodule.js');

		setlocale(LC_ALL, i18n::get_locale());
	}
	
	/**
	 * Generate an RSS-feed.
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
	
	public function getRSSFeed() {
		return News::get()
			->filter(array('Live' => 1))
			->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
			->sort("Created", "DESC")
			->limit(10);
	}
	
	/**
	 * General getter. Should this even be public?
	 * We escape the tags here, otherwise things bug out with the meta-tags.
	 * @return boolean or object. If object, we are successfully on a page. If boolean, it's baaaad.
	 */
	public function getNews(){
		$Params = $this->getURLParams();
		if($Params['Action'] == 'show'){
			if(is_numeric($Params['ID'])){
				$news = News::get()->filter(array(
					'ID' => $Params['ID'],
					'Live' => 1
				))->first();
				$link = $this->Link('show/').$news->URLSegment;
				$this->redirect($link, 301);
				return false;
			}
			else{
				$news = News::get()->filter(
					array(
						'URLSegment' => $Params['ID'], // Oh the irony!
						'Live' => 1
					)
				)
				->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'));
				if($news->count() > 0){
					return $news->first();
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
	 * Get the correct tags.
	 * It would be kinda weird to get the incorrect tags, would it?
	 * @param type $news This is for the TaggedItems template. To only show the tags. Seemed logic to me.
	 * @return type DataObject or DataList with tags or news.
	 */
	public function getTags($news = false){
		$Params = $this->getURLParams();
		if(isset($Params['ID']) && $Params['ID'] != null){
			$tagItems = Tag::get()->filter(array('URLSegment' => $Params['ID']))->first();
			if($tagItems->News()->count() > 0 && !$news){
				return $tagItems;
			}
			elseif($tagItems->News()->count() > 0 && $news){
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $tagItems->ID)
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'));
				return $news;
			}				
			else{
				$this->redirect('tag');
			}
		}
		$return = Tag::get();
		return $return;
	}
	
	/**
	 * Just return this. currentNewsItem should fix it. This one is for show.
	 * @return object this. Forreal! Or, redirect if getNews() returns false.
	 */
	public function show() {
		if($this->getNews()){
			return $this;
		}
		else{
			$this->redirect($this->Link());
		}
	}
	
	public function tag(){
		if($this->getTags()){
			return $this;
		}
	}
	
	public function tags(){
		if($this->getTags()){
			return $this;
		}
	}
	
	/**
	 * If we're on a newspage, we need to get the news, or else.... OOOHHHH!
	 * @return object of the item.
	 */
	public function currentNewsItem(){
		$siteConfig = SiteConfig::current_site_config();
		if ($newsItem = $this->getNews()) {
			$newsItem->AllowComments = $siteConfig->Comments;
			return($newsItem);
		}	
	}
	
	public function currentTag(){
		return $this->getTags();
	}

	/**
	 * @todo pagination pagination pagination pagination pagination pagination
	 * @return object The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		if($allEntries = News::get()->filter(array('Live' => 1))->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))){
			return $allEntries;
		}
		return null;
	}
	
	/**
	 * I'm tired of writing comments!
	 * Ok, well, here, I build a form. Nice, huh?
	 * @todo add a very, very, very left aligned field to detect spambots? Saves on akismet maybe?
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
		$field[] = TextField::create('Name', 'Name');
		$field[] = TextField::create('Title', 'Comment title');
		$field[] = TextField::create('Email', 'E-mail');
		$field[] = TextField::create('URL', 'Website');
		$field[] = TextareaField::create('Comment', 'Comment');
		
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
	 * I put it in a zakje!
	 * And also check if it's no double-post. Limited to 60 seconds, but it can be differed.
	 * I wonder if this is XSS safe? The saveInto does this for me, right?
	 * @param array $data
	 * @param object $form 
	 */
	public function CommentStore($data, $form){
		$data['Comment'] = Convert::raw2sql($data['Comment']);
		if(!Comment::get()->where('Comment LIKE \'' . $data['Comment'] . '\' AND ABS(TIMEDIFF(NOW(), Created)) < 60')->count()){
			$comment = new Comment();
			$form->saveInto($comment);
			$comment->NewsID = $data['NewsID'];
			$comment->write();
		}
		$this->redirectBack();
	}
}
