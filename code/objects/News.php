<?php
/**
 * The news.
 * Sit back and relax, this might take a while.
 * History is NOT supported. Only the URLSegment is being tracked. This makes it a bit more simplistic.
 * 
 * Ow, yes, translatable... Can I ponder on that please?
 * 
 * @package News/blog module
 * @author Simon 'Sphere' 
 */
class News extends DataObject { // implements IOGObject{ // optional for OpenGraph support

	public static $db = array(
		'Title' => 'Varchar(255)',
		// Author is a troublemaker. Please tell me, 
		// should I either auto-set the username from currentmember, 
		// or use the textfield I'm using now (LAZY!)
		'Author' => 'Varchar(255)',
		'URLSegment' => 'Varchar(255)',
		'Content' => 'HTMLText',
		'PublishFrom' => 'Date',
		'Tweeted' => 'Boolean(false)',
		'Live' => 'Boolean(true)',
		'Commenting' => 'Boolean(true)',
	);
	
	public static $has_one = array(
		'NewsHolderPage' => 'NewsHolderPage',
		'Impression' => 'Image',
	);
	
	public static $has_many = array(
		'Comments' => 'Comment',
		'Renamed' => 'Renamed',
		'SlideshowImages' => 'SlideshowImage',
	);
	
	public static $many_many = array(
		'Tags' => 'Tag',
	);

	public static $default_sort = 'IF(PublishFrom, PublishFrom, News.Created) DESC';
//	Disable the above and enable the line below, if you want to used the cached on created option. Although I think it doesn't add much
//	public static $default_sort = 'News.Created DESC';
	
	/**
	 * Set defaults. Commenting (show comments if allowed in siteconfig) is default to true.
	 * @var type 
	 */
	public static $defaults = array(
		'Commenting' => true,
	);
	
	/**
	 * And I forgot the index. On large databases, this is a small performance improvement.
	 * @var type 
	 */
	public static $indexes = array(
		'URLSegment' => true,
	);

	/**
	 * Define singular name translatable
	 * @return type Singular name
	 */
	public function singular_name() {
		if (_t($this->class . '.SINGULARNAME')) {
			return _t($this->class . '.SINGULARNAME');
		} else {
			return parent::singular_name();
		} 
	}
	
	/**
	 * Define plural name translatable
	 * @return type Plural name
	 */
	public function plural_name() {
		if (_t($this->class . '.PLURALNAME')) {
			return _t($this->class . '.PLURALNAME');
		} else {
			return parent::plural_name();
		}   
	}
	
	/**
	 * Define sumaryfields;
	 * @todo obey translations
	 * @return string Make summaryfields translatable
	 */
	public function summaryFields() {
		$summaryFields = array(
			'Title' => _t($this->class . '.TITLE', 'Titel'),
			'Author' => _t($this->class . '.AUTHOR', 'Author'),
			'fetchPublish' => _t($this->class . 'PUBLISH', 'Publish date'),
		);
		$translatable = Translatable::get_existing_content_languages('NewsHolderPage');
		if(count($translatable) > 1){
			$summaryFields['getLocale'] = _t($this->class . '.LOCALE', 'Language');
		}
		$this->extend('summary_fields', $summaryFields);

		return $summaryFields;
	}
	
	/**
	 * Define translatable searchable fields
	 * @return array Searchable Fields translatable
	 */
	public function searchableFields(){
		$searchableFields = parent::searchableFields();
		$searchableFields = array(
			'Title' => array(
				'field'  => 'TextField',
				'filter' => 'PartialMatchFilter',
				'title'  => _t($this->class . '.TITLE','Title')
			),
		);
		/**
		 * THIS DOES NOT WORK YET!
		 * For some reason, forcing a source fails :(
		 * For now, just type the locales, ok?
		 */
		$translatable = Translatable::get_existing_content_languages('NewsHolderPage', true);
		if(count($translatable) > 1){
			$searchableFields['NewsHolderPage.Locale'] = array(
				'title' => _t($this->class . '.LOCALE', 'Language'),
			);
		}		
		return $searchableFields;
	}
	
	/**
	 * This is for sorting the newsitems by either one of them. Keep it clean!
	 * @return type
	 */
	public function fetchPublish(){
		if(!$this->PublishFrom){
			return $this->Created;
		}
		return $this->PublishFrom;
	}
	
	public function getCMSFields() {
		/**
		 * This is to adress the Author-issue. As described in the db-field declaration
		 */
		if(!$this->ID){
			$this->Author = Member::currentUser()->FirstName . ' ' . Member::currentUser()->Surname;
		}
		/**
		 * If there are multiple translations available, add the field.
		 * This better not break?
		 */
		$translatable = Translatable::get_existing_content_languages('NewsHolderPage');
		if(count($translatable) > 1){
			$translate = DropdownField::create('Lang', _t($this->class . '.LOCALE', 'Locale'), $translatable);
		}
		else{
			$translate = LiteralField::create('Doh', '');
		}
		
		$fields = FieldList::create(TabSet::create('Root'));
		
		$fields->addFieldsToTab(
			'Root',
			Tab::create(
				'Main',
				_t($this->class . '.MAIN', 'Main'),
				$help = ReadonlyField::create('dummy', _t($this->class . '.HELPTITLE', 'Help'), _t($this->class . '.HELP', 'It is important to know, the publish-date does require the publish checkbox to be set! Publish-date is optional. Also, it won\'t auto-tweet when it goes live!')),
				$text = TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				$translate,
				$html = HTMLEditorField::create('Content', _t($this->class . '.CONTENT', 'Content')),
				$auth = TextField::create('Author', _t($this->class . '.AUTHOR', 'Author')),
//				Disabled temporarily since it seems to be a bit an issue.
//				$date = DateField::create('PublishFrom', _t($this->class . '.PUBDATE', 'Publish from this date on'))->setConfig('showcalendar', true),
				$live = CheckboxField::create('Live', _t($this->class . '.PUSHLIVE', 'Publish (Note, even with publish-date, it must be checked!)')),
				$alco = CheckboxField::create('Commenting', _t($this->class . '.COMMENTING', 'Allow comments on this item')),
				$uplo = UploadField::create('Impression', _t($this->class . '.IMPRESSION', 'Impression')),
				$tags = CheckboxSetField::create('Tags', _t($this->class . '.TAGS', 'Tags'), Tag::get()->map('ID', 'Title'))
			)
		);
		if($this->ID){
			$fields->addFieldToTab(
				'Root.Main',
				new LiteralField('Dummy',
					'<div id="Dummy" class="field readonly">
	<label class="left" for="Form_ItemEditForm_Dummy">Link</label>
	<div class="middleColumn">
	<span id="Form_ItemEditForm_Dummy" class="readonly">
		<a href="'.$this->AbsoluteLink().'" target="_blank">'.$this->AbsoluteLink().'</a>
	</span>
	</div>
	</div>'
				),
				'Title'
			);
		}
		$fields->addFieldToTab(
			'Root',
			Tab::create(
				'Comments',
				_t($this->class . '.COMMENTS', 'Comments'),
				GridField::create(
					'Comment', 
					_t($this->class . '.COMMENTS', 'Comments'),
					$this->Comments(), 
					GridFieldConfig_RelationEditor::create()
				)
			)
		);
		$gridFieldConfig = GridFieldConfig_RecordEditor::create();
		$gridFieldConfig->addComponent(new GridFieldBulkEditingTools());
		$gridFieldConfig->addComponent(new GridFieldBulkImageUpload()); 
		$gridFieldConfig->addComponent(new GridFieldSortableRows('SortOrder'));
		$fields->addFieldToTab(
			'Root',
			Tab::create(
				'SlideshowImages',
				_t($this->class . '.SLIDE', 'Slideshow'),
				$gridfield = GridField::create(
					'SlideshowImage',
					_t($this->class . '.IMAGES', 'Slideshow Images'),
					$this->SlideshowImages()
						->sort('SortOrder'), 
					$gridFieldConfig)
			)
		);
		
		return($fields);
	}

	/**
	 * Why does this, again, not work on live, but does it work on dev?
	 * @param type $includeTitle boolean
	 * @return string of a whole heap of meta-data
	 */
	public function MetaTags($includeTitle = true){
		$tags = "";
		$tags .= "<meta name=\"keywords\" content=\"" . Convert::raw2att($this->NewsHolderPage()->MetaKeywords . ',' . str_replace(' ', ',',$this->Title)) . "\" />\n";
		$tags .= "<meta name=\"description\" content=\"" . Convert::raw2att($this->NewsHolderPage()->MetaDescription . ' ' . $this->Title) . "\" />\n";
		
		if($this->ExtraMeta) { 
			$tags .= $this->ExtraMeta . "\n";
		} 
		
		if(Permission::check('CMS_ACCESS_CMSMain') && in_array('CMSPreviewable', class_implements($this))) {
			$tags .= "<meta name=\"x-page-id\" content=\"{$this->ID}\" />\n";
			$tags .= "<meta name=\"x-cms-edit-link\" content=\"" . $this->CMSEditLink() . "\" />\n";
		}
		$this->extend('MetaTags', $tags);
		return $tags;
	}
	
	/**
	 * Setup available locales.
	 * Yes, again, this is beta and not working yet :(
	 * @todo Frikkin' fix multi-language support!
	 * @return type 
	 */
	public function getLocale(){
		$parent = $this->NewsHolderPage();
		if($parent && class_exists('Translatable')){
			if($parent->Locale){
				return $parent->Locale;
			}
		}
	}

	/**
	 * Free guess on what this button does.
	 */
	public function Link() {
		if ($newsHolderPage = SiteTree::get()->filter(array("ClassName" => 'NewsHolderPage'))->first()) {
			return($newsHolderPage->Link('show').'/'.$this->URLSegment);
		}
	}


	/**
	 * This is quite handy, for meta-tags and such.
	 * @param type $action string, the added URLSegment, the actual function that'll return the news.
	 * @return type link. To the item.
	 */
	public function AbsoluteLink($action = 'show/'){
		if($Page = $this->NewsHolderPage()){
			return(Director::absoluteURL($Page->Link($action)). $this->URLSegment);
		}		
	}
		
	/**
	 * All the upcoming OG-functions are related to the OG module.
	 * This bugs in live, works in development. Shoot me?
	 * @return type image, or, if not available, it's holder-page's image.
	 */
	public function getOGImage(){
		if($this->Impression()->ID > 0){
			return Director::getAbsFile($this->Impression()->Filename);
		}
		else{
			return Director::getAbsFile($this->NewsHolderPage()->Impression()->Filename);
		}
	}
	
	/**
	 * Guess
	 * @return type String
	 */
	public function getOGTitle(){
		return $this->Title;
	}
	
	/**
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one. 
	 * @todo Actually implement the translatable part :)
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if(!$this->Lang){
			$page = NewsHolderPage::get()->first();
			$this->NewsHolderPageID = $page->ID;
		}
		else{
			fb($this->Lang);
			$page = Translatable::get_one_by_locale('NewsHolderPage', $this->Lang);
			fb($page->ID);exit;
			$this->NewsHolderPage = $page->ID;
		}
		if (!$this->URLSegment || ($this->isChanged('Title') && !$this->isChanged('URLSegment'))){
			if($this->ID > 0){
				$Renamed = new Renamed();
				$Renamed->OldLink = $this->URLSegment;
				$Renamed->NewsID = $this->ID;
				$Renamed->write();
			}
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->Title);
			if(strpos($this->URLSegment, 'page-') === false){
				$nr = 1;
				while($this->LookForExistingURLSegment($this->URLSegment)){
					$this->URLSegment .= '-'.$nr++;
				}
			}
		}
	}
	
	public function onAfterWrite(){
		parent::onAfterWrite();
		$siteConfig = SiteConfig::current_site_config();
		/**
		 * This is related to another module of mine.
		 * Check it at my repos: Silverstripe-Social.
		 * It auto-tweets your new Newsitem. If the TwitterController exists ofcourse.
		 * It doesn't auto-tweet if the publish-date is in the future. Also, it won't tweet when it's that date!
		 */
		if($this->Live && ($this->PublishDate = null || $this->PublishDate <= date('Y-m-d')) && !$this->Tweeted && $siteConfig->TweetOnPost){
			if(class_exists('TwitterController')){
				TwitterController::postTweet($this->Title, $this->AbsoluteLink());
				$this->Tweeted = true;
				$this->write();
			}
		}
		/**
		 * I should implement the Post To Facebook option here.
		 */
	}

	/**
	 * test whether the URLSegment exists already on another Newsitem
	 * @return boolean if urlsegment already exists yes or no.
	 */
	public function LookForExistingURLSegment($URLSegment) {
		return(News::get()->filter(array("URLSegment" => $URLSegment))->exclude(array("ID" => $this->ID))->count() != 0);
	}
	
	/**
	 * Ehhh, this needs fixing for SS3.
	 * So yes, you can.
	 */
	public function canCreate($member = null) {
		return(true);
	}

	public function canEdit($member = null) {
		return(true);
	}

	public function canDelete($member = null) {
		return(true);
	}

	public function canView($member = null) {
		return(true);
	}

}
