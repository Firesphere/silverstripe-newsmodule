<?php

/**
 * Tagging for your news, so you can categorize everything and optionally even create a tagcloud.
 * In the Holderpage, there's an option for the tags to view everything by tag.
 *
 * @author Simon 'Sphere' Erkelens
 * @package News/Blog module
 * @todo implement translations?
 * @todo fix getCMSFields() function.
 *
 * StartGeneratedWithDataObjectAnnotator
 * @property string Title
 * @property string Description
 * @property string URLSegment
 * @property string Locale
 * @property int SortOrder
 * @property int ImpressionID
 * @method Image Impression
 * @method ManyManyList|News[] News
 * @mixin TagCMSExtension
 * EndGeneratedWithDataObjectAnnotator
 */
class Tag extends DataObject
{
	/** @var array $db database-fields */
	private static $db = array(
		'Title'       => 'Varchar(255)',
		'Description' => 'HTMLText',
		'URLSegment'  => 'Varchar(255)',
		'Locale'      => 'Varchar(10)', // NOT YET SUPPORTED (I think)
		'SortOrder'   => 'Int',
	);

	/** @var array $has_one relationships. */
	private static $has_one = array(
		'Impression' => 'Image',
	);

	/** @var array $belongs_many_many of belongings */
	private static $belongs_many_many = array(
		'News' => 'News',
	);

	/**
	 * CMS seems to ignore this unless sortable is enabled.
	 * Input appreciated.
	 * @var string $default_sort sortorder of this object.
	 */
	private static $default_sort = 'SortOrder ASC';

	/**
	 * Create indexes.
	 * @var array $indexes Index for the database
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
		if (_t('Tag.SINGULARNAME')) {
			return _t('Tag.SINGULARNAME');
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
		if (_t('Tag.PLURALNAME')) {
			return _t('Tag.PLURALNAME');
		} else {
			return parent::plural_name();
		}
	}

	/**
	 * Setup the fieldlabels correctly.
	 * @param boolean $includerelations
	 * @return array The fieldlabels
	 */
	public function fieldLabels($includerelations = true)
	{
		$labels = parent::fieldLabels($includerelations);
		$tagLabels = array(
			'Title'       => _t('Tag.TITLE', 'Title'),
			'Description' => _t('Tag.DESCRIPTION', 'Description'),
			'Impression'  => _t('Tag.IMPRESSION', 'Impression image'),
		);

		return array_merge($tagLabels, $labels);
	}

	/**
	 * @todo I still have to fix that translatable, remember? ;)
	 */
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if (!$this->URLSegment || ($this->isChanged('Title') && !$this->isChanged('URLSegment'))) {
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->Title);
			if (strpos($this->URLSegment, 'page-') === false) { // It might occur, and we don't want page-0, page-1 etc. in the list!
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
	 * test whether the URLSegment exists already on another tag
	 * @param string $URLSegment
	 * @return boolean if urlsegment already exists yes or no.
	 */
	public function LookForExistingURLSegment($URLSegment)
	{
		return (Tag::get()->filter(array("URLSegment" => $URLSegment))->exclude(array("ID" => $this->ID))->count() != 0);
	}

	/**
	 * Free guess on what this button does.
	 * @param string $action The required action
	 * @return string|boolean Link to this object or false if no holderpage is found..
	 */
	public function Link($action = 'tag/')
	{
		if ($config = SiteConfig::current_site_config()->TagAction) {
			$action = $config . '/';
		}
		if ($Page = NewsHolderPage::get()->first()) {
			return ($Page->Link($action . $this->URLSegment));
		}

		return false;
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param string $action The added URLSegment, the actual function that'll return the tag.
	 * @return string Link. To the item. (Yeah, I'm super cereal here)
	 */
	public function AbsoluteLink()
	{
		if ($Page = $this->Link()) {
			return (Director::absoluteURL($Page));
		}
	}

	public function activeNews()
	{
		$now = SS_DateTime::now()->Format('Y-m-d');

		return $this->News()
			->filter(array('Live' => true))
			->exclude(array('PublishFrom:GreaterThan' => $now));
	}

	/**
	 * Permissions
	 */
	public function providePermissions()
	{
		return array(
			'CREATE_TAG' => array(
				'name'     => _t('Tag.PERMISSION_CREATE_DESCRIPTION', 'Create tags'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help'     => _t('Tag.PERMISSION_CREATE_HELP', 'Permission required to create new tags.')
			),
			'EDIT_TAG'   => array(
				'name'     => _t('Tag.PERMISSION_EDIT_DESCRIPTION', 'Edit tags'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help'     => _t('Tag.PERMISSION_EDIT_HELP', 'Permission required to edit existing tags.')
			),
			'DELETE_TAG' => array(
				'name'     => _t('Tag.PERMISSION_DELETE_DESCRIPTION', 'Delete tags'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help'     => _t('Tag.PERMISSION_DELETE_HELP', 'Permission required to delete existing tags.')
			),
			'VIEW_TAG'   => array(
				'name'     => _t('Tag.PERMISSION_VIEW_DESCRIPTION', 'View tags'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help'     => _t('Tag.PERMISSION_VIEW_HELP', 'Permission required to view existing tags.')
			),
		);
	}

	public function canCreate($member = null)
	{
		return (Permission::checkMember($member, array('CREATE_TAG', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canEdit($member = null)
	{
		return (Permission::checkMember($member, array('EDIT_TAG', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canDelete($member = null)
	{
		return (Permission::checkMember($member, array('DELETE_TAG', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canView($member = null)
	{
		return (Permission::checkMember($member, array('VIEW_TAG', 'CMS_ACCESS_NewsAdmin')));
	}

}
