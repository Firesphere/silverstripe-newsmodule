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
 * @todo Order this. The order of the functions does not make sense.
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
		'Lang' => 'Boolean(false)',
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
	);
	
	public static $belongs_many_many = array(
		'Tags' => 'Tag',
	);

	public static $default_sort = 'Created DESC';
	
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
		if (_t($this->class . '.SINGULARNAME')) {
			return _t($this->class . '.SINGULARNAME');
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
			'Title' => 'Titel',
			'Author' => 'Author',
			'Created' => 'Created',
		);
		if(array_search('Translatable', SiteTree::$extensions)){
			$summaryFields['getLocale'] = _t($this->class . '.LANG', 'Language');
		}
		$this->extend('summary_fields', $summaryFields);

		return $summaryFields;
	}
	
	/**
	 * Define translatable searchable fields
	 * @return array Searchable Fields translatable
	 */
	public function searchableFields(){
		$searchableFields = array(
			'Title' => array(
				'field'  => 'TextField',
				'filter' => 'PartialMatchFilter',
				'title'  => _t($this->class . '.TITLE','Title')
			),
		);
		if(array_search('Translatable', SiteTree::$extensions)){
			$searchableFields['NewsHolderPageID'] = array(
				'field' => 'DropdownField',
				'title' => _t($this->class . '.LOCALE', 'Language'),
				'filter' => 'ExactMatchFilter',

			);
		}

		$this->extend('searchable_fields', $searchableFields);
		
		return $searchableFields;
	}

	/**
	 * Why do I have to do this???
	 * We can't feed an array directly into the searchfields, so, we have to make a workaround.
	 * Buh...
	 * @todo cleanup and make it working.
	 * @param type $_params
	 * @return type 
	 */
	public function scaffoldSearchFields($_params = null){
		$fields = parent::scaffoldSearchFields();
		if(array_search('Translatable', SiteTree::$extensions)){
			$data = new SQLQuery();
			$data->select(array('ID', 'Locale'));
			$data->from = array('SiteTree');
			$data->where = array('ClassName = \'NewsHolderPage\'', 'Status = \'Published\'');
			$array = $data->execute();
			if(count($array) > 0){
				if(count($array->map()) > 1){
					$locales = i18n::get_common_locales();
					$return = array('' => _t($this->class . '.SELECTSOME', '--Select a locale--'));
					$array = $array->map('ID', 'Locale');
					foreach($array as $key => $value){
						if(substr($value, 0, 2) != '--'){
							$return[$key] = $locales[$value];
						}
					}
					unset($value);
				}
			}


			if(count($array) > 1){
				foreach($fields->items as $item => $field){
					if($field->name == 'NewsHolderPageID'){
						$field->source = $return;
					}
				}
			}
		}
		return $fields;
	}
	
	public function getCMSFields() {
		$fields = FieldList::create(TabSet::create('Root'));
		
		$fields->addFieldsToTab(
			'Root',
			Tab::create(
				'Main',
				_t('MAIN', 'Main'),
				$help = ReadonlyField::create('dummy', _t($this->class . '.HELPTITLE', 'Help'), _t($this->class . '.HELP', 'It is important to know, the publish-date does require the publish checkbox to be set! Publish-date is optional. Also, it won\'t auto-tweet when it goes live!')),
				$text = TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				$html = HTMLEditorField::create('Content', _t($this->class . '.CONTENT', 'Content')),
				$auth = TextField::create('Author', _t($this->class . '.AUTHOR', 'Author')),
				$date = DateField::create('PublishFrom', _t($this->class . '.PUBDATE', 'Publish from this date on'))->setConfig('showcalendar', true),
				$live = CheckboxField::create('Live', _t($this->class . '.PUSHLIVE', 'Publish (Note, even with publish-date, it must be checked!)')),
				$alco = CheckboxField::create('Commenting', _t($this->class . '.COMMENTS', 'Allow comments on this item')),
				$uplo = UploadField::create('Impression', _t($this->class . '.IMPRESSION', 'Impression')),
				$tags = CheckboxSetField::create('Tags', 'Tags', Tag::get()->map('ID', 'Title'))
			)
		);
		$fields->addFieldToTab(
			'Root.Comments',
			GridField::create(
				'Comment', 
				_t($this->class . '.COMMENTS', 'Comments'),
				$this->Comments(), 
				GridFieldConfig_RelationEditor::create()
			)
		);
		return($fields);
	}

	
	/**
	 * Setup available locales.
	 * Yes, again, this is beta and not working yet :(
	 * @return type 
	 */
	public function getLocale(){
		if($this->NewsHolderPage()->ID){
			$parent = SiteTree::get()->filter(array('ID' => $this->NewsHolderPage()->ID))->first();
			$locales = i18n::get_common_locales();
			if($parent->Locale){
				return $locales[$parent->Locale];
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
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one. 
	 * @todo Actually implement the translatable part :)
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if(!$this->NewsHolderPageID){
			$page = NewsHolderPage::get()->first();
			$this->NewsHolderPageID = $page->ID;
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
		 */
		if($this->Live && ($this->PublishDate = null || $this->PublishDate <= date('Y-m-d')) && !$this->Tweeted && $siteConfig->TweetOnPost){
			if(class_exists('TwitterController')){
				TwitterController::postTweet($this->Title, $this->AbsoluteLink());
				$this->Tweeted = true;
				$this->write();
			}
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
