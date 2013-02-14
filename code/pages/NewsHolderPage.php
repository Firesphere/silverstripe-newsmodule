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
	 * @param type $arguments
	 * @return null 
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
		'rss',
		'CommentForm',
		'CommentStore',
	);
	
	/**
	 * Meta! This is so Meta!
	 * Yes, Meta stuff here :)
	 * All just setting, no returning needed since it's $this.
	 */
	public function MetaTitle(){
		if($news = $this->getNews()){      
			$this->Title = $news->Title . ' - ' . $this->Title;
		}
	}
	
	public function MetaKeywords(){
		if($news = $this->getNews()){      
			$this->MetaKeywords .= implode(', ', explode(' ', $news->Title));
		}		
	}
	
	public function MetaDescription(){
		if($news = $this->getNews()){      
			$this->MetaDescription .= ' '.$news->Title;
		}		
	}
	
	public function MetaTags(){
		return $this->MetaTags();
	}

	/**
	 * Annoying date features. Override! 
	 */
	public function init() {
		parent::init();
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
		return News::get()->filter(array('Live' => 1))->sort("Created", "DESC")->limit(10);
	}
	
	/**
	 * General getter. Should this even be public?
	 * @return boolean or object. If object, we are successfully on a page. If boolean, it's baaaad.
	 */
	public function getNews(){
		$Params = $this->getURLParams();
		if(is_numeric($Params['ID'])){
			$news = News::get()->filter(array(
				'ID' => is_numeric($Params['ID']) ? $Params['ID'] : $id,
				'Live' => 1
			))->first();
			$link = $this->Link('show/').$news->URLSegment;
			$this->redirect($link, 301);
			return false;
		}
		else{
			$news = News::get()->filter(array(
				'URLSegment' => $Params['ID'], // Oh the irony!
				'Live' => 1
			));
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

	/**
	 * @todo pagination pagination pagination pagination pagination pagination
	 * @return object The newsitems, sliced by the amount of length. Set to wished value
	 */
	public function allNews(){
		if($allEntries = News::get()->filter(array('Live' => 1))){
			return $allEntries;
		}
		return null;
	}
	
	/**
	 * I'm tired of writing comments!
	 * Ok, well, here, I build a form. Nice, huh?
	 * @return boolean 
	 */
	public function CommentForm(){
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
		 * I should really use
		 * $this->request->postVar('NewsID')
		 * But, this is for checks, we need the ID to store the comment correctly.
		 */
		if(!isset($_POST['NewsID'])){
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
