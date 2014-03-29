<?php
/**
 * The news.
 * Sit back and relax, this might take a while.
 * History is NOT supported. Only the URLSegment is being tracked. This makes it a bit more simplistic.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 * @method NewsHolderPage NewsHolderPages() this NewsItem belongs to
 * @method Image Impression() the Impression for this NewsItem
 * @method Comment Comment() Comments on this NewsItem
 * @method Renamed Renamed() changed URLSegments
 * @method SlideshowImage SlideshowImages() for the slideshow-feature
 * @method Tag Tags() Added Tags for this Item.
 */
class News extends DataObject { // implements IOGObject{ // optional for OpenGraph support

	private static $db = array(
		'Title' => 'Varchar(255)',
		/** Author might be handled via Member, but that's not useful if you want a non-member to post in his/her name */
		'Author' => 'Varchar(255)',
		'URLSegment' => 'Varchar(255)',
		'Synopsis' => 'Text',
		'Content' => 'HTMLText',
		'PublishFrom' => 'Date',
		'Tweeted' => 'Boolean(false)',
		'FBPosted' => 'Boolean(false)',
		'Live' => 'Boolean(true)',
		'Commenting' => 'Boolean(true)',
		/** This is for the external location of a link */
		'Type' => 'Enum("news,external,download","news")',
		'External' => 'Varchar(255)',
	);
	
	private static $has_one = array(
		'Impression' => 'Image',
		/** If you want to have a download-file */
		'Download' => 'File',
		/** Generic helper to have Author-specific pages */
		'AuthorHelper' => 'AuthorHelper',
	);
	
	private static $has_many = array(
		'Comments' => 'Comment',
		'Renamed' => 'Renamed',
		'SlideshowImages' => 'SlideshowImage',
	);
	
	private static $many_many = array(
		'Tags' => 'Tag',
	);
	
	private static $belongs_many_many = array(
		'NewsHolderPages' => 'NewsHolderPage',
	);
	
	private static $summary_fields = array();
	
	private static $searchable_fields = array();

	private static $default_sort = 'PublishFrom DESC';
	
	/**
	 * Set defaults. Commenting (show comments if allowed in siteconfig) is default to true.
	 * @var array $defaults. Commenting is true, SiteConfig overrides this!
	 */
	private static $defaults = array(
		'Commenting' => true,
	);
	
	/**
	 * On large databases, this is a small performance improvement.
	 * @var array $indexes.
	 */
	private static $indexes = array(
		'URLSegment' => true,
	);
	
	/**
	 * Store the siteconfig in a local variable, saves queries.
	 * @var type SiteConfig
	 */
	protected $current_siteconfig;
	
	/**
	 * 
	 * @param array|null $record This will be null for a new database record.  Alternatively, you can pass an array of
	 * field values.  Normally this contructor is only used by the internal systems that get objects from the database.
	 * @param boolean $isSingleton This this to true if this is a singleton() object, a stub for calling methods.
	 *                             Singletons don't have their defaults set.
	 * @param News $model The model we're instantiating.
	 * @todo Fix this a cleaner way, it's overkill.
	 */
	public function __construct($record = null, $isSingleton = false, $model = null) {
		parent::__construct($record, $isSingleton, $model);
		$this->current_siteconfig = SiteConfig::current_site_config();
		if(!$this->ID && Member::currentUser()) {
			$name =  Member::currentUser()->FirstName . ' ' . Member::currentUser()->Surname;
			$this->Author = $name;
		}
	}

	/**
	 * Define singular name translatable
	 * @return string Singular name
	 */
	public function singular_name() {
		if (_t('News.SINGULARNAME')) {
			return _t('News.SINGULARNAME');
		} else {
			return parent::singular_name();
		} 
	}
	
	/**
	 * Define plural name translatable
	 * @return string Plural name
	 */
	public function plural_name() {
		if (_t('News.PLURALNAME')) {
			return _t('News.PLURALNAME');
		} else {
			return parent::plural_name();
		}
	}

	/**
	 * Define sumaryfields;
	 * @return array $summaryFields
	 */
	public function summaryFields() {
		$summaryFields = parent::summaryFields();
		$summaryFields = array_merge(
			$summaryFields, 
			array(
				'Title' => _t('News.TITLE', 'Title'),
				'Author' => _t('News.AUTHOR', 'Author'),
				'PublishFrom' => _t('News.PUBLISH', 'Publish from'),
			)
		);
		return $summaryFields;
	}
	
	/**
	 * Define translatable searchable fields
	 * @return array $searchableFields translatable
	 */
	public function searchableFields(){
		$searchableFields = parent::searchableFields();
		unset($searchableFields['PublishFrom']);
		$searchableFields['Title'] = array(
				'field'  => 'TextField',
				'filter' => 'PartialMatchFilter',
				'title'  => _t('News.TITLE','Title')
			);
		$searchableFields['Author'] = array(
			'field'  => 'TextField',
			'filter' => 'PartialMatchFilter',
			'title'  => _t('News.AUTHOR','Author')
		);
		return $searchableFields;
	}
	
	/**
	 * Setup the fieldlabels and their translation.
	 * @param type $includerelations
	 * @return array $labels an array of the FieldLabels
	 */
	public function fieldLabels($includerelations = true) {
		$labels = parent::fieldLabels($includerelations);
		$newsLabels = array(
			'Title'		  => _t('News.TITLE', 'Title'),
			'Author'	  => _t('News.AUTHOR', 'Author'),
			'Synopsis'	  => _t('News.SUMMARY', 'Summary/Abstract'),
			'Content'	  => _t('News.CONTENT', 'Content'),
			'PublishFrom'	  => _t('News.PUBDATE', 'Publish from'),
			'Live'		  => _t('News.PUSHLIVE', 'Published'),
			'Commenting'	  => _t('News.COMMENTING', 'Allow comments on this item'),
			'Type'		  => _t('News.NEWSTYPE', 'Type of item'),
			'External'	  => _t('News.EXTERNAL', 'External link'),
			'Download'	  => _t('News.DOWNLOAD', 'Downloadable file'),
			'Impression'	  => _t('News.IMPRESSION', 'Impression image'),
			'Comments'	  => _t('News.COMMENTS', 'Comments'),
			'SlideshowImages' => _t('News.SLIDE', 'Slideshow'),
			'Tags'		  => _t('News.TAGS', 'Tags'),
			'NewsHolderPages' => _t('News.LINKEDPAGES', 'Linked pages'),
			'Help'		  => _t('News.BASEHELPLABEL', 'Help')
		);
		return array_merge($newsLabels, $labels);
	}
	
	/**
	 * Free guess on what this button does.
	 * @todo make this work on multilanguage sites.
	 * @return string Link to this object.
	 */
	public function Link($action = 'show/') {
		if($this->current_siteconfig->ShowAction) {
			$action = $this->current_siteconfig->ShowAction;
		}
		$Page = $this->NewsHolderPages()->count();
		if ($Page = $this->NewsHolderPages()->first()) {
			return($Page->Link($action.'/'.$this->URLSegment));
		}
		return false;
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param string $action The added URLSegment, the actual function that'll return the news.
	 * @return string Link. To the item. (Yeah, I'm super cereal here)
	 */
	public function AbsoluteLink(){
		if($Page = $this->Link()){
			return(Director::absoluteURL($Page));
		}		
	}

	/**
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one.
	 * The NewsHolderPage should NEVER be doubled.
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if(!class_exists('Translatable') || !$this->NewsHolderPages()->count()){
			$page = NewsHolderPage::get()->first();
			$this->NewsHolderPages()->add($page);
		}
		if(!$this->Type || $this->Type == ''){
			$this->Type = 'news';
		}
		/** Set PublishFrom to today to prevent errors with sorting. New since 2.0, backward compatible. */
		if(!$this->PublishFrom){
			$this->PublishFrom = date('Y-m-d');
		}
		/**
		 * Make sure the link is valid.
		 */
		if(substr($this->External,0,4) != 'http' && $this->External != ''){
			$this->External = 'http://'.$this->External;
		}
		$this->setURLSegment();
		$this->setAuthorData();
	}
	
	public function onAfterWrite(){
		parent::onAfterWrite();
		$siteConfig = SiteConfig::current_site_config();
		/**
		 * This is related to another module of mine.
		 * Check it at my repos: Silverstripe-Social.
		 * It auto-tweets your new Newsitem. If the TwitterController exists ofcourse.
		 * It doesn't auto-tweet if the publish-date is in the future. Also, it won't tweet when it's that date!
		 * @todo refactor this to a facebook/twitter oAuth method that a dev spent more time on developing than I did on my Social-module.
		 */
		if(class_exists('TwitterController')){
			if($this->Live && $this->PublishDate <= date('Y-m-d') && !$this->Tweeted && $siteConfig->TweetOnPost){
				$this->Tweeted = true;
				$this->write();
			}
		}
	}

	/**
	 * Setup the URLSegment for this item and create a Renamed Object if it's a rename-action.
	 */
	private function setURLSegment() {
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
	
	/**
	 * test whether the URLSegment exists already on another Newsitem
	 * @return boolean URLSegment already exists yes or no.
	 */
	private function LookForExistingURLSegment($URLSegment) {
		return(News::get()->filter(
				array("URLSegment" => $URLSegment)
			)->exclude(
				array("ID" => $this->ID)
			)->count() != 0);
	}
	
	/**
	 * Create the author if non-existing yet, and set his/her ID to this item.
	 */
	private function setAuthorData() {
		$this->Author = trim($this->Author);
		$author = AuthorHelper::get()->filter('OriginalName', trim($this->Author));
		if($author->count() == 0){
			$author = AuthorHelper::create();
			$author->OriginalName = trim($this->Author);
			$author->write();
		}
		else{
			$author = $author->first();
		}
		$this->AuthorID = $author->ID;
	}
	
	public function getComments() {
		return $this->Comments()->filter(array('AkismetMarked' => 0));
	}

	/**
	 * Get the year this object is created.
	 * @return string $yearItems String of 4 numbers representing the year
	 */
	public function getYearCreated(){
		$yearItems = date('Y', strtotime($this->PublishFrom));
		return $yearItems;
	}

	/**
	 * Get the month this object is published
	 * @return string $monthItems double-digit representation of the month this object was published.
	 */
	public function getMonthCreated(){
		$monthItems = date('F', strtotime($this->PublishFrom));
		return $monthItems;
	}

        /**
         * Why oh why does $Date.Nice still not use i18n::get_date_format()??
	 * // If I recall correctly, this is a known issue with i18n class.
	 * @todo Fix this. It bugs out, for example, English notation(?) is MMM d Y, the three M make it go JunJunJun 1, 2013. BAD!
	 * Temporary fix: Forced to use d-m-Y
         * @return string
         */
        public function getPublished() {
		return date('d-m-Y', strtotime($this->PublishFrom));
		$format = i18n::get_date_format();
		return $this->dbObject('PublishFrom')->Format($format);
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
