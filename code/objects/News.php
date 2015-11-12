<?php

/**
 * The news.
 * Sit back and relax, this might take a while.
 * History is NOT supported. Only the URLSegment is being tracked. This makes it a bit more simplistic.
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 * @property string Title
 * @property string Author
 * @property string URLSegment
 * @property Text Synopsis
 * @property HTMLText Content
 * @property Date PublishFrom
 * @property Boolean Tweeted
 * @property Boolean FBPosted
 * @property Boolean Live
 * @property Boolean Commenting
 * @property Enum Type
 * @property string External
 * @property Int ImpressionID
 * @property Int DownloadID
 * @property Int AuthorHelperID
 * @method Image Impression() the Impression for this NewsItem
 * @method File Download() Get the downloadable file
 * @method AuthorHelper AuthorHelper() Get the author of this post
 * @method Comment Comments() Comments on this NewsItem
 * @method Renamed Renamed() changed URLSegments
 * @method SlideshowImage SlideshowImages() for the slideshow-feature
 * @method Tag Tags() Added Tags for this Item
 * @method NewsHolderPage NewsHolderPages() The pages this item is linked to
 */
class News extends DataObject implements PermissionProvider
{
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
	 * @var array $defaults . Commenting is true, SiteConfig overrides this!
	 */
	private static $defaults = array(
		'Commenting' => true,
	);

	/**
	 * On large databases, this is a small performance improvement.
	 * @var array $indexes .
	 */
	private static $indexes = array(
		'URLSegment' => true,
	);

	/**
	 * Define singular name translatable
	 * @return string Singular name
	 */
	public function singular_name()
	{
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
	public function plural_name()
	{
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
	public function summaryFields()
	{
		$summaryFields = parent::summaryFields();
		$summaryFields = array_merge(
			$summaryFields, array(
				'Title' => _t('News.TITLE', 'Title'),
				'Author' => _t('News.AUTHOR', 'Author'),
				'PublishFrom' => _t('News.PUBLISH', 'Publish from'),
				'Status' => _t('News.STATUS', 'Status'),
			)
		);
		return $summaryFields;
	}

	/**
	 * Define translatable searchable fields
	 * @return array $searchableFields translatable
	 */
	public function searchableFields()
	{
		$searchableFields = parent::searchableFields();
		unset($searchableFields['PublishFrom']);
		$searchableFields['Title'] = array(
			'field' => 'TextField',
			'filter' => 'PartialMatchFilter',
			'title' => _t('News.TITLE', 'Title')
		);
		$searchableFields['Author'] = array(
			'field' => 'TextField',
			'filter' => 'PartialMatchFilter',
			'title' => _t('News.AUTHOR', 'Author')
		);
		return $searchableFields;
	}

	/**
	 * Setup the fieldlabels and their translation.
	 * @param Boolean $includerelations
	 * @return array $labels an array of the FieldLabels
	 */
	public function fieldLabels($includerelations = true)
	{
		$labels = parent::fieldLabels($includerelations);
		$newsLabels = array(
			'Title' => _t('News.TITLE', 'Title'),
			'Author' => _t('News.AUTHOR', 'Author'),
			'Synopsis' => _t('News.SUMMARY', 'Summary/Abstract'),
			'Content' => _t('News.CONTENT', 'Content'),
			'PublishFrom' => _t('News.PUBDATE', 'Publish from'),
			'Live' => _t('News.PUSHLIVE', 'Published'),
			'Commenting' => _t('News.COMMENTING', 'Allow comments on this item'),
			'Type' => _t('News.NEWSTYPE', 'Type of item'),
			'External' => _t('News.EXTERNAL', 'External link'),
			'Download' => _t('News.DOWNLOAD', 'Downloadable file'),
			'Impression' => _t('News.IMPRESSION', 'Impression image'),
			'Comments' => _t('News.COMMENTS', 'Comments'),
			'SlideshowImages' => _t('News.SLIDE', 'Slideshow'),
			'Tags' => _t('News.TAGS', 'Tags'),
			'NewsHolderPages' => _t('News.LINKEDPAGES', 'Linked pages'),
			'Help' => _t('News.BASEHELPLABEL', 'Help')
		);
		return array_merge($newsLabels, $labels);
	}

	/**
	 * Free guess on what this button does.
	 * @todo make this work on multilanguage sites.
	 * @param string $action
	 * @return string Link to this object.
	 */
	public function Link($action = 'show/')
	{
		if ($config = SiteConfig::current_site_config()->ShowAction) {
			$action = $config . '/';
		}
		if ($Page = $this->NewsHolderPages()->first()) {
			return ($Page->Link($action . $this->URLSegment));
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$this->extend('generateCMSFields', $fields);

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param string $action
	 * @return string Link. To the item. (Yeah, I'm super cereal here)
	 */
	public function AbsoluteLink($action = 'show/')
	{
		return (Director::absoluteURL($this->Link($action)));
	}

	public function AllowComments()
	{
		return (SiteConfig::current_site_config()->Comments && $this->Commenting);
	}

	/**
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one.
	 * The NewsHolderPage should NEVER be doubled.
	 */
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		/** Check if we have translatable and a NewsHolderPage. If no HolderPage available, skip (Create an orphan) */
		if (!$this->NewsHolderPages()->count()) {
			if (!class_exists('Translatable') && $page = NewsHolderPage::get()->first()) {
				$this->NewsHolderPages()->add($page);
			}
		}
		if (!$this->Type || $this->Type == '') {
			$this->Type = 'news';
		}
		/** Set PublishFrom to today to prevent errors with sorting. New since 2.0, backward compatible. */
		if (!$this->PublishFrom) {
			$this->PublishFrom = SS_Datetime::now()->Rfc2822();
		}
		/**
		 * Make sure the link is valid.
		 */
		if (substr($this->External, 0, 4) != 'http' && $this->External != '') {
			$this->External = 'http://' . $this->External;
		}
		$this->setURLValue();
		$this->setAuthorData();
	}

	/**
	 * {@inheritdoc}
	 */
	public function onAfterWrite()
	{
		parent::onAfterWrite();
		$siteConfig = SiteConfig::current_site_config();
		/**
		 * This is related to another module of mine.
		 * Check it at my repos: Silverstripe-Social.
		 * It auto-tweets your new Newsitem. If the TwitterController exists ofcourse.
		 * It doesn't auto-tweet if the publish-date is in the future. Also, it won't tweet when it's that date!
		 * @todo refactor this to a facebook/twitter oAuth method that a dev spent more time on developing than I did on my Social-module.
		 */
		if (class_exists('TwitterController')) {
			$date = SS_DateTime::now()->Format('Y-m-d');
			if ($this->Live && $this->PublishDate <= $date && !$this->Tweeted && $siteConfig->TweetOnPost) {
				$this->Tweeted = true;
				$this->write();
			}
		}
	}

	/**
	 * Setup the URLSegment for this item and create a Renamed Object if it's a rename-action.
	 */
	private function setURLValue()
	{
		if (!$this->URLSegment || ($this->isChanged('Title') && !$this->isChanged('URLSegment'))) {
			if ($this->ID > 0) {
				$Renamed = new Renamed();
				$Renamed->OldLink = $this->URLSegment;
				$Renamed->NewsID = $this->ID;
				$Renamed->write();
			}
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->Title);
			if (strpos($this->URLSegment, 'page-') === false) {
				$nr = 1;
				$URLSegment = $this->URLSegment;
				while ($this->LookForExistingURLSegment($URLSegment)) {
					$URLSegment = $this->URLSegment . '-' . $nr++;
				}
				$this->URLSegment = $URLSegment;
			}
		}
	}

	/**
	 * test whether the URLSegment exists already on another Newsitem
	 * @param string $URLSegment chosen URLSegment
	 * @return boolean URLSegment already exists yes or no.
	 */
	private function LookForExistingURLSegment($URLSegment)
	{
		return (News::get()
				->filter(array("URLSegment" => $URLSegment))
				->exclude(array("ID" => $this->ID))
				->count() != 0);
	}

	/**
	 * Create the author if non-existing yet, and set his/her ID to this item.
	 */
	private function setAuthorData()
	{
		$this->Author = trim($this->Author);
		$nameParts = explode(' ', $this->Author);
		foreach ($nameParts as $key => $namePart) {
			if ($namePart == '') {
				unset($nameParts[$key]);
			}
		}
		$this->Author = implode(' ', $nameParts);
		$author = AuthorHelper::get()->filter('OriginalName', trim($this->Author))->first();
		if (!$author->exists()) {
			$author = AuthorHelper::create();
			$author->OriginalName = trim($this->Author);
			$author->write();
		}
		$this->AuthorHelperID = $author->ID;
	}

	/**
	 * Get the allowed comments
	 * @return DataList with comments
	 */
	public function getAllowedComments()
	{
		return $this->Comments()
			->filter(array('AkismetMarked' => false, 'Visible' => true));
	}

	/**
	 * Get the year this object is created.
	 * @return Int $yearItems String of 4 numbers representing the year
	 */
	public function getYearCreated()
	{
		return $this->dbObject('PublishFrom')->Format('Y');
	}

	/**
	 * Get the month this object is published
	 * @return string $monthItems double-digit representation of the month this object was published.
	 */
	public function getMonthCreated()
	{
		return $this->dbObject('PublishFrom')->Format('F');
	}

	/**
	 * Create a date-string based on the locale. Looks better.
	 * @return string
	 * @todo this needs some work and improvement
	 */
	public function getPublished()
	{
//		i18n::get_date_format();
//		$locale = i18n::get_locale();
//		$date = new Zend_Date();
//		$date->set($this->PublishFrom, null, $locale);
//		return substr($date->getDate($locale), 0, -9);
	}

	/**
	 * Permissions
	 */
	public function providePermissions()
	{
		return array(
			'CREATE_NEWS' => array(
				'name' => _t('News.PERMISSION_CREATE_DESCRIPTION', 'Create newsitems'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_CREATE_HELP', 'Permission required to create new newsitems.')
			),
			'EDIT_NEWS' => array(
				'name' => _t('News.PERMISSION_EDIT_DESCRIPTION', 'Edit newsitems'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_EDIT_HELP', 'Permission required to edit existing newsitems.')
			),
			'DELETE_NEWS' => array(
				'name' => _t('News.PERMISSION_DELETE_DESCRIPTION', 'Delete newsitems'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_DELETE_HELP', 'Permission required to delete existing newsitems.')
			),
			'VIEW_NEWS' => array(
				'name' => _t('News.PERMISSION_VIEW_DESCRIPTION', 'View newsitems'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_VIEW_HELP', 'Permission required to view existing newsitems.')
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function canCreate($member = null)
	{
		return (Permission::checkMember($member, array('CREATE_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function canEdit($member = null)
	{
		return (Permission::checkMember($member, array('EDIT_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function canDelete($member = null)
	{
		return (Permission::checkMember($member, array('DELETE_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function canView($member = null)
	{
		return (Permission::checkMember($member, array('VIEW_NEWS', 'CMS_ACCESS_NewsAdmin')) || $this->Live == 1);
	}

	/**
	 * Helper function to determine if this News object is already published or not
	 *
	 * @return bool
	 */
	public function isPublished()
	{
		return $this->Live;
	}

	/**
	 * Returns if the news item is published or not
	 *
	 * @return string
	 */
	public function getStatus()
	{
		$published = $this->isPublished() ? _t('News.IsPublished', 'published') : _t('News.IsUnpublished', 'not published');
		if ($this->PublishFrom > SS_Datetime::now()->Rfc2822() && $this->isPublished()) {
			$published = _t('News.InQueue', 'Awaiting publishdate');
		}
		return $published;
	}

	/**
	 * Publishes a news item
	 *
	 * @throws ValidationException
	 */
	public function doPublish()
	{
		if (!$this->canEdit()) {
			throw new ValidationException(_t('News.PublishPermissionFailure', 'No permission to publish or unpublish news item'));
		}
		$this->Live = true;
		$this->write();
	}

	/**
	 * Unpublishes an news item
	 *
	 * @throws ValidationException
	 */
	public function doUnpublish()
	{
		if (!$this->canEdit()) {
			throw new ValidationException(_t('News.PublishPermissionFailure', 'No permission to publish or unpublish news item'));
		}
		$this->Live = false;
		$this->write();
	}

}
