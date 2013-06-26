<?php
/**
 * The news.
 * Sit back and relax, this might take a while.
 * History is NOT supported. Only the URLSegment is being tracked. This makes it a bit more simplistic.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 * @todo Semantics
 * @todo Cleanup and integration with newsholderpage.
 * @method NewsHolderPage NewsHolderPage this NewsItem belongs to
 * @method Impression Image the Impression for this NewsItem
 * @method Comments Comment Comments on this NewsItem
 * @method Renamed Renamed changed URLSegments
 * @method SlideshowImages SlideshowImage Images for the slideshow-feature
 * @method Tags Tag Added Tags for this Item.
 */
class News extends DataObject { // implements IOGObject{ // optional for OpenGraph support

	private static $db = array(
		'Title' => 'Varchar(255)',
		/**
		 * Author is a troublemaker. Please tell me, 
		 * should I either auto-set the username from currentmember, 
		 * or use the textfield I'm using now (Lazy implementation).
		 */
		'Author' => 'Varchar(255)',
		'URLSegment' => 'Varchar(255)',
		'Synopsis' => 'Text',
		'Content' => 'HTMLText',
		'PublishFrom' => 'Date',
		'Tweeted' => 'Boolean(false)',
		'Live' => 'Boolean(true)',
		'Commenting' => 'Boolean(true)',
		'Locale' => 'Varchar(10)',
		/** This is for the external location of a link */
		'Type' => 'Enum("news,external,download","news")',
		'External' => 'Varchar(255)',
	);
	
	private static $has_one = array(
		'NewsHolderPage' => 'NewsHolderPage',
		'Impression' => 'Image',
		/** If you want to have a download-file */
		'Download' => 'File',
	);
	
	private static $has_many = array(
		'Comments' => 'Comment',
		'Renamed' => 'Renamed',
		'SlideshowImages' => 'SlideshowImage',
	);
	
	private static $many_many = array(
		'Tags' => 'Tag',
	);

	private static $default_sort = 'IF(PublishFrom, PublishFrom, News.Created) DESC';
	/**
	 * Disable the above and enable the line below, if you want to use the cached feature
	 * Although I don't think the caching helps, If you want to use it, don't use the PublishFrom and comment the sort above.
	 * And uncomment the sort below.
	 */
//	public static $default_sort = 'News.Created DESC';
	
	/**
	 * Set defaults. Commenting (show comments if allowed in siteconfig) is default to true.
	 * @var type array of defaults. Commenting is true, SiteConfig overrides this!
	 */
	private static $defaults = array(
		'Commenting' => true,
	);
	
	/**
	 * On large databases, this is a small performance improvement.
	 * @var type array of indexes.
	 */
	private static $indexes = array(
		'URLSegment' => true,
	);
	
	/** 
	 * Why am I casting here? Probably not needed.
	 * @redundant Go die in a fire casting. I don't need you.
	 * @var array() Really? Yes, really.
	 */
	private static $casting = array(
		'FilterDate' => 'Datetime',
	);
	
	/**
	 * Define singular name translatable
	 * @return type string Singular name
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
	 * @return type string Plural name
	 */
	public function plural_name() {
		if (_t($this->class . '.PLURALNAME')) {
			return _t($this->class . '.PLURALNAME');
		} else {
			return parent::plural_name();
		}
	}
	
		
	public function getFilterDate(){
		if($this->PublishFrom != null){
			return $this->PublishFrom;
		}
		return $this->Created;
	}

	/**
	 * Define sumaryfields;
	 * @return array of summaryfields
	 */
	public function summaryFields() {
		$summaryFields = array(
			'Title' => _t($this->class . '.TITLE', 'Titel'),
			'Author' => _t($this->class . '.AUTHOR', 'Author'),
			'Created' => _t($this->class . 'PUBLISH', 'Publish date'),
		);
		$pages = NewsHolderPage::get();
		if($pages->count() > 1){
			$summaryFields['NewsHolderPage.Title'] = _t($this->class . '.PARENTPAGE', 'Parent holderpage');
		}
		if(class_exists('Translatable')){
			$translatable = Translatable::get_existing_content_languages('NewsHolderPage');
			if(count($translatable) > 1){
				$summaryFields['fetchLocale'] = _t($this->class . '.LOCALE', 'Language');
			}
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
		unset($searchableFields['NewsHolderPage.Title']);
		$searchableFields['Title'] = array(
				'field'  => 'TextField',
				'filter' => 'PartialMatchFilter',
				'title'  => _t($this->class . '.TITLE','Title')
			);
		$pages = NewsHolderPage::get();
		if($pages->count() > 1){
			$searchableFields['NewsHolderPageID'] = array(
				'title' => _t($this->class . '.PARENTPAGE', 'Parent holderpage'),
				'filter' => 'PartialMatchFilter',
				'field' => 'DropdownField',
			);
		}
		/**
		 * Add the translatable dropdown if we can translate.
		 */
		if(class_exists('Translatable')){
			$translatable = Translatable::get_existing_content_languages('NewsHolderPage');
			if(count($translatable) > 1){
				$searchableFields['Locale'] = array(
					'title' => _t($this->class . '.LOCALE', 'Language'),
					'filter' => 'ExactMatchFilter',
					'field' => 'DropdownField',
				);
			}
		}
		return $searchableFields;
	}
	
	/**
	 * Setup the translatable dropdown sources.
	 * @param type $_params
	 * @return type array of fields
	 */
	public function scaffoldSearchFields($_params = null){
		$fields = parent::scaffoldSearchFields($_params);
		/**
		 * If there's a locale-field, fill it. Weird that this can't be done in the original function.
		 */
		if($fields->fieldByName('Locale') != null){
			$fields->fieldByName('Locale')
				->setSource(
					array_merge(
						array('' => _t($this->class . '.ANY', 'Any language')), 
						Translatable::get_existing_content_languages('NewsHolderPage')
					)
				);
		}
		if($fields->fieldByName('NewsHolderPageID') != null){
			$source = NewsHolderPage::get();
			$source = $source->map('ID', 'Title');
			$fields->fieldByName('NewsHolderPageID')
				->setSource(
					$source
				);
		}
		return $fields;
	}
	
	/**
	 * This is for sorting the newsitems by either one of them. Keep it clean!
	 * Or not. Could work any way.
	 * @return Date of publish
	 */
	public function fetchPublish(){
		if(!$this->PublishFrom){
			return $this->Created;
		}
		return $this->PublishFrom;
	}
	
	/**
	 * Unless you're really motivated. Don't read this. It's too much.
	 * @return FieldList with the Fields required. Who would've guessed?!
	 */
	public function getCMSFields() {
		$siteConfig = SiteConfig::current_site_config();
		/**
		 * Configuration options from SiteConfig
		 */
		$typeArray = array(
			'news' => _t($this->class . '.NEWSITEMTYPE', 'Newsitem'),
		);
		$link = LiteralField::create('External', '');
		$file = LiteralField::create('Download', '');
		if($siteConfig->AllowExternals){
			$typeArray['external'] = _t($this->class . '.EXTERNALTYPE', 'External link');
			$link = TextField::create('External', _t($this->class . '.EXTERNAL', 'External link'));
		}
		if($siteConfig->AllowDownloads){
			$typeArray['download'] = _t($this->class . '.DOWNLOADTYPE', 'Download');
			$file = UploadField::create('Download', _t($this->class . '.DOWNLOAD', 'Downloadable file'));
		}
		if(count($typeArray) > 1){
			$type = OptionsetField::create('Type', _t($this->class . '.NEWSTYPE', 'Type of item'), $typeArray, $this->Type);
		}
		else{
			$type = LiteralField::create('Type', '');
		}
		if($siteConfig->UseAbstract){
			$summ = TextareaField::create('Synopsis', _t($this->class . '.SUMMARY', 'Summary/Abstract'));
		}
		else{
			$summ = LiteralField::create('Synopsis', '');
		}
		/**
		 * This is to adress the Author-issue. As described in the db-field declaration.
		 * Also, setup the tags-field. Relations can't be saved if the object doesn't exist yet.
		 */
		if(!$this->ID){
			$this->Author = Member::currentUser()->FirstName . ' ' . Member::currentUser()->Surname;
			$tags = ReadonlyField::create('Tags', _t($this->class . '.TAGS', 'Tags'), _t($this->class . '.TAGAFTERID', 'Tags can be added after the newsitem is saved once'));
			$this->Type = 'news';
		} else {
			$tags = CheckboxSetField::create('Tags', _t($this->class . '.TAGS', 'Tags'), Tag::get()->map('ID', 'Title'));
		}

		/**
		 * If there are multiple translations available, add the field.
		 * If there's just one locale, just create a literalfield.
		 * And if there's no translatable at all, we create a literalfield as well, because we need the field in the list.
		 * This better not break?
		 */
		if(class_exists('Translatable')){
			$translatable = Translatable::get_existing_content_languages('NewsHolderPage');
			if(count($translatable) > 1){
				$translate = DropdownField::create('Locale', _t($this->class . '.LOCALE', 'Locale'), $translatable);
			}
			else{
				$translate = LiteralField::create('Locale', '');
			}
		}
		else{
			$translate = LiteralField::create('Locale', '');
		}
		/** Allow for multiple root newsholderpages. */
		/** @todo fix a neater way to display this in the admin. Requires a new ModelAdmin extension-module. TBA(tm) */
		$multiple = LiteralField::create('NoMultiple', '');
		$HolderPages = NewsHolderPage::get();
		if($HolderPages->count() > 1){
			$multiple = DropdownField::create('NewsHolderPageID', _t($this->class . '.HOLDERPAGE', 'Parent holderpage'), $HolderPages->map('ID', 'Title'));
		}
		/** Setup new root tab */
		$fields = FieldList::create(TabSet::create('Root'));
		
		$fields->addFieldsToTab(
			'Root', // what tab
			Tab::create(
				'Main', // Name
				_t($this->class . '.MAIN', 'Main'), // Title
				/** The fields */
				$text = TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				$translate,
				$multiple,
				$type,
				$summ,
				$link,
				$html = HTMLEditorField::create('Content', _t($this->class . '.CONTENT', 'Content')),
				$file,
				$auth = TextField::create('Author', _t($this->class . '.AUTHOR', 'Author')),
				$date = DateField::create('PublishFrom', _t($this->class . '.PUBDATE', 'Publish from this date on'))->setConfig('showcalendar', true),
				$live = CheckboxField::create('Live', _t($this->class . '.PUSHLIVE', 'Publish (Note, even with publish-date, it must be checked!)')),
				$alco = CheckboxField::create('Commenting', _t($this->class . '.COMMENTING', 'Allow comments on this item')),
				$uplo = UploadField::create('Impression', _t($this->class . '.IMPRESSION', 'Impression')),
				$tags
			)
		);

		/**
		 * Add a link to the frontpage version of the item.
		 * The following items are all has_one or has_many relations.
		 * No use for showing them initially.
		 */
		if($this->ID){
			$fields->addFieldToTab(
				'Root.Main',
				LiteralField::create('Dummy',
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
			
			/**
			 * If commenting is allowed globally, show the comment-tab.
			 */
			if($siteConfig->Comments){
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
			}
			/**
			 * Note the requirements! Otherwise, things might break!
			 * If the Slideshow is enabled, show it's gridfield and features
			 */
			if($siteConfig->EnableSlideshow){
				$gridFieldConfig = GridFieldConfig_RecordEditor::create();
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
			}
		}
		
		/**
		 * If UncleCheese's module Display Logic is available, upgrade the visible fields!
		 * @todo make this actually work. Contact @_UncleCheese_
		 */
		if(class_exists('DisplayLogicFormField') && count($typeArray) > 1){
			$file->hideUnless('Type')->isEqualTo('download');
			$link->hideUnless('Type')->isEqualTo('external');
			$html->hideUnless('Type')->isEqualTo('news');
		}
		
		return($fields);
	}

	/**
	 * Setup available locales.
	 * @return type ArrayList of available locale's.
	 */
	public function fetchLocale(){
		$locales = Translatable::get_existing_content_languages();
		return($locales[$this->NewsHolderPage()->Locale]);
	}

	/**
	 * Free guess on what this button does.
	 */
	public function Link($action = 'show/') {
		if ($Page = $this->NewsHolderPage()) {
			return($Page->Link($action).$this->URLSegment);
		}
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param $action string, the added URLSegment, the actual function that'll return the news.
	 * @return link. To the item. (Yeah, I'm super cereal here)
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
	 * Guess again.
	 * @return type String
	 */
	public function getOGTitle(){
		return $this->Title;
	}
	
	/**
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one.
	 * The NewsHolderPage should NEVER be doubled.
	 * @todo Actually implement the translatable part :)
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if((!$this->Locale || !class_exists('Translatable') || $this->Locale == '') && !$this->NewsHolderPageID){
			$page = NewsHolderPage::get()->first();
			$this->NewsHolderPageID = $page->ID;
		}
		elseif(!$this->NewsHolderPageID){
			$page = Translatable::get_one_by_locale('NewsHolderPage', $this->Locale);
			$this->NewsHolderPageID = $page->ID;
		}
		if(!$this->Type || $this->Type == ''){
			$this->Type = 'news';
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
		if(class_exists('TwitterController')){
			if($this->Live && ($this->PublishDate = null || $this->PublishDate <= date('Y-m-d')) && !$this->Tweeted && $siteConfig->TweetOnPost){
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
	 * Returns the year and month this news item was posted in, this is for Grouping in template..
	 * @return string of year|month. With 4 characters, unless it's the year 10000. Or a month, ofcourse.
	 */
	public function getYearCreated(){
		$yearItems = date('Y', strtotime($this->Created));
		return $yearItems;
	}

	public function getMonthCreated(){
		$monthItems = date('F', strtotime($this->Created));
		return $monthItems;
	}

	/**
	 * Permissions
	 */
	public function canCreate($member = null) {
		return(Permission::checkMember($member, 'CMS_ACCESS_NewsAdmin'));
	}

	public function canEdit($member = null) {
		return(Permission::checkMember($member, 'CMS_ACCESS_NewsAdmin'));
	}

	public function canDelete($member = null) {
		return(Permission::checkMember($member, 'CMS_ACCESS_NewsAdmin'));
	}

	public function canView($member = null) {
		return(Permission::checkMember($member, 'CMS_ACCESS_NewsAdmin') || $this->Live == 1);
	}

}
