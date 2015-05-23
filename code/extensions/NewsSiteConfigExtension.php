<?php

/**
 * Add some settings to the siteconfig. Pretty easy, right?
 *
 * @package News/blog module
 * @author Sphere
 * @method Image DefaultImage() The default impression image.
 * @method Image DefaultGravatarImage() The default gravatar image.
 * @method Folder NewsRootFolder() Root folder for slideshow images.
 * @todo Work this out a bit better.
 * @todo fix the fieldlabels
 */
class NewsSiteConfigExtension extends DataExtension
{
	/** @var array $db Contains all the extra's we need for setting everything up. */
	private static $db = array(
		/** Default options */
		'UseAbstract'      => 'Boolean(true)',
		'PostsPerPage'     => 'Int',
		'TweetOnPost'      => 'Boolean(false)',
		/** Slideshow options */
		'EnableSlideshow'  => 'Boolean(true)',
		'SlideshowInitial' => 'Boolean(true)',
		'SlideshowSize'    => 'Varchar(15)',
		/** Comment options */
		'Comments'         => 'boolean(true)',
		'NewsEmail'        => 'Varchar(255)',
		'MustApprove'      => 'boolean(true)',
		'Gravatar'         => 'boolean(true)',
		'DefaultGravatar'  => 'Varchar(255)',
		'GravatarSize'     => 'Int',
		'AkismetKey'       => 'Varchar(255)',
		'NoscriptSecurity' => 'Boolean(true)',
		'ExtraSecurity'    => 'Boolean(true)',
		/** External options */
		'AllowExternals'   => 'Boolean(true)',
		'AllowDownloads'   => 'Boolean(true)',
		'ReturnExternal'   => 'Boolean(true)',
		/** Security settings */
		'AllowAuthors'     => 'Boolean(false)',
		'AllowTags'        => 'Boolean(true)',
		'AllowExport'      => 'Boolean(false)',
		'AllowSlideshow'   => 'Boolean(true)',
		/** Social data */
		'TwitterAccount'   => 'Varchar(255)',
		/** URL Mapping */
		'TagAction'        => 'Varchar(255)',
		'TagsAction'       => 'Varchar(255)',
		'ShowAction'       => 'Varchar(255)',
		'AuthorAction'     => 'Varchar(255)',
		'ArchiveAction'    => 'Varchar(255)',
	);

	/** @var array $has_one Contains all the one-to-many relations */
	private static $has_one = array(
		'NewsRootFolder'       => 'Folder',
		'DefaultImage'         => 'Image',
		'DefaultGravatarImage' => 'Image',
	);
	private static $defaults = array(
		/** URL Mapping */
		'TagAction'     => 'tag',
		'TagsAction'    => 'tags',
		'ShowAction'    => 'show',
		'AuthorAction'  => 'author',
		'ArchiveAction' => 'archive',
		'PostPerPage'   => 10
	);

	/** @var array $config_tabs the tabs available for the user to config. */
	private static $config_tabs = array(
		'NewsTab',
		'ExternalTab',
		'CommentTab',
		'SlideshowTab',
		'HelpTab',
	);

	/** @var array $admin_tabs The Admin-specific tabs. @todo Make some fields admin-only? */
	private static $admin_tabs = array(
		'URLMappingTab',
		'SecurityTab',
	);

	/**
	 * A foldername relative to /assets,
	 * where all uploaded files are stored by default.
	 * Can be overwritten in db using NewsRootFolder
	 *
	 * @config
	 * @var string
	 */
	private static $uploads_folder = "news";

	/**
	 * Update the SiteConfig with the news-settings.
	 * The tabs are pushed into arrays, because it works better than adding them one by one.
	 * @param FieldList $fields of current FieldList of SiteConfig
	 */
	public function updateCMSFields(FieldList $fields)
	{
		/** Only allow authors or higher! */
		$fields->addFieldToTab(
			'Root', // What tab
			TabSet::create(
				'Newssettings', _t('NewsSiteConfigExtension.NEWSCOMMENTS', 'News settings')
			)
		);
		if (($this->owner->AllowAuthors && $this->owner->canEdit(Member::currentUser())) || Member::currentUser()->inGroup('administrators')) {
			$userTabs = array();
			foreach (self::$config_tabs as $tab) {
				$userTabs[] = $this->$tab();
			}
			$fields->addFieldsToTab('Root.Newssettings', $userTabs);
		} else {
			$fields->addFieldToTab('Root.Newssettings', HeaderField::create('NoPermissions', _t('NewsSiteConfigExtension.PERMISSIONERROR', 'You do not have the permission to edit these settings')));
		}
		if (Member::currentUser()->inGroup('administrators')) {
			$adminTabs = array();
			foreach (self::$admin_tabs as $tab) {
				$adminTabs[] = $this->$tab();
			}
			$fields->addFieldsToTab('Root.Newssettings', $adminTabs, 'Help');
		}
	}

	/**
	 * Setup the tabs for the user
	 * All return a Tab.
	 */
	protected function NewsTab()
	{
		/** General news settings */
		return Tab::create(
			'News', _t('NewsSiteConfigExtension.NEWS', 'News'), CheckboxField::create('UseAbstract', _t('NewsSiteConfigExtension.ABSTRACT', 'Use abstract/summary')), CheckboxField::create('TweetOnPost', _t('NewsSiteConfigExtension.TWEETPOST', 'Tweet after posting a new item')), // Requires Firesphere/silverstripe-social
			NumericField::create('PostsPerPage', _t('NewsSiteConfigExtension.PPP', 'Amount of posts per page')), UploadField::create('DefaultImage', _t('NewsSiteConfigExtension.DEFAULTIMPRESSION', 'Default impression image for newsitems'))
		);
	}

	protected function ExternalTab()
	{
		/** External linking options */
		return Tab::create(
			'External', _t('NewsSiteConfigExtension.EXTERNAL', 'External linking'), CheckboxField::create('AllowExternals', _t('NewsSiteConfigExtension.ALLOWEXT', 'Allow external links')), CheckboxField::create('AllowDownloads', _t('NewsSiteConfigExtension.ALLOWDOWN', 'Allow downloads')), CheckboxField::create('ReturnExternal', _t('NewsSiteConfigExtension.RETURNEXT', 'Make externals open in a new tab/window'))
		);
	}

	protected function CommentTab()
	{
		/** Comment settings */
		return Tab::create(
			'Comments', _t('NewsSiteConfigExtension.COMMENTSSETTINGS', 'Comments'), CheckboxField::create('Comments', _t('NewsSiteConfigExtension.COMMENTS', 'Allow comments on items')), CheckboxField::create('MustApprove', _t('NewsSiteConfigExtension.APPROVE', 'Comments must be approved')), EmailField::create('NewsEmail', _t('NewsSiteConfigExtension.NEWSMAIL', 'Send email notification of a comment to me')), CheckboxField::create('Gravatar', _t('NewsSiteConfigExtension.GRAVATAR', 'Display Gravatar image of commenter')), TextField::create('DefaultGravatar', _t('NewsSiteConfigExtension.GRAVURL', 'Default Gravatar image if commenter doesn\'t have one')), UploadField::create('DefaultGravatarImage', _t('NewsSiteConfigExtension.UPLOADGRAVATAR', 'Or upload a default Gravatar image')), NumericField::create('GravatarSize', _t('NewsSiteConfigExtension.GRAVSIZE', 'Size of the Gravatar image (e.g. 32 for a 32x32 image)')), TextField::create('AkismetKey', _t('NewsSiteConfigExtension.AKISMET', 'Akismet API key')), CheckboxField::create('ExtraSecurity', _t('NewsSiteConfigExtension.SPAMPROTECTION', 'Use an extra field for spam protection')), CheckboxField::create('NoscriptSecurity', _t('NewsSiteConfigExtension.NOSCRIPTSPAM', 'Use a noscript field for spam protection'))
		);
	}

	protected function SlideshowTab()
	{
		/** Slideshow settings */
		/** @todo use display logic to hide stuff when slideshow is disabled */
		if (class_exists('RootFolder') && News::has_extension('RootFolder')) {
			// we use folder per root extension and define the root_folder in config.yml
			$rootFolderName = Config::inst()->get('News', 'folder_root')
				?: _t('NewsSiteConfigExtension.SLIDESHOWFOLDERNOTSET', 'not set');
			$folderTree = LiteralField::create("NewsRootFolderDisabled", _t('NewsSiteConfigExtension.SLIDESHOWFOLDERCONFIG', '<strong>Found folder per root extension</strong><br />This will automatically create an upload folder per news item.<br />Current root folder: "<strong>{rootFolderName}</strong>"<br />Set News.folder_root your config.yml to change this', '', array('rootFolderName' => $rootFolderName)));
		} else {
			//get a tree listing with only folder, no files
			$folderTree = TreeDropdownField::create("NewsRootFolderID", _t('NewsSiteConfigExtension.SLIDESHOWFOLDER', 'Folder where images are saved to; defaults to "news"'), 'Folder');
			$folderTree->setChildrenMethod('ChildFolders');
		}


		return Tab::create(
			'Slideshowsettings', _t('NewsSiteConfigExtension.SLIDESHOWSETTINGS', 'Slideshow'), CheckboxField::create('EnableSlideshow', _t('NewsSiteConfigExtension.SLIDESHOW', 'Allow the use of slideshow feature')), $folderTree, CheckboxField::create('SlideshowInitial', _t('NewsSiteConfigExtension.SLIDEINITIAL', 'Show only the first image')), TextField::create('SlideshowSize', _t('NewsSiteConfigExtension.SLIDESIZE', 'Maximum size of the full-size images. E.g. 1024x768'))
		);
	}

	protected function HelpTab()
	{
		/** Help tab (Unfinished) */
		return Tab::create(
			'Help', _t('NewsSiteConfigExtension.HELP', 'Help'), ReadonlyField::create('generalhelp', _t('NewsSiteConfigExtension.NEWSHELP', 'News help'), _t('NewsSiteConfigExtension.NEWSHELPTEXT', 'In the news settings tab, you can set general settings like if you want to use an abstract, tweet after post (this is on the issuelist!) Fields are quite understandable by itself.')), ReadonlyField::create('externalhelp', _t('NewsSiteConfigExtension.EXTERNALHELP', 'External help'), _t('NewsSiteConfigExtension.EXTERNALHELPTEXT', 'Allow or disallow content authors to link to external items and set how to handle external items. Open a new tab/window or open in the same tab/window.')), ReadonlyField::create('commenthelp', _t('NewsSiteConfigExtension.COMMENTHELP', 'Comment help'), _t('NewsSiteConfigExtension.COMMENTHELPTEXT', 'Comment help is tbd.')), ReadonlyField::create('slideshowhelp', _t('NewsSiteConfigExtension.SLIDESHOWHELP', 'Slideshow help'), _t('NewsSiteConfigExtension.SLIDESHOWHELPTEXT', 'Slideshow settings, like what to do. TBD'))
		);
	}

	/**
	 * Setup the Admin-only tabs.
	 */
	protected function URLMappingTab()
	{
		/** For admin only */
		return Tab::create(
			'URL Mapping', _t('NewsSiteConfigExtension.MAPPING', 'URL Mapping'), LiteralField::create('mappinghelp', _t('NewsSiteConfigExtension.MAPPINGHELP', 'Set the URL Parameters to handle things to your wishing, e.g. /news/newsitem instead of /news/show. Don\'t use "latest" or doubles!')), TextField::create('ShowAction', _t('NewsSiteConfigExtension.SHOWMAPPING', 'URL Parameter to show an item')), TextField::create('TagAction', _t('NewsSiteConfigExtension.TAGMAPPING', 'URL Parameter to show a tag')), TextField::create('TagsAction', _t('NewsSiteConfigExtension.TAGSMAPPING', 'URL Parameter to show all tags')), TextField::create('AuthorAction', _t('NewsSiteConfigExtension.AUTHORMAPPING', 'URL Parameter to show an authorpage')), TextField::create('ArchiveAction', _t('NewsSiteConfigExtension.ARCHIVEMAPPING', 'URL Parameter to show the archive'))
		);
	}

	protected function SecurityTab()
	{
		return Tab::create(
			'Security', _t('NewsSiteConfigExtension.SEC', 'Security'), CheckboxField::create('AllowAuthors', _t('NewsSiteConfigExtension.ALLOWAUTHOR', 'Allow content authors to edit news settings')),
//			CheckboxField::create('AllowTags', _t('NewsSiteConfigExtension.ALLOWTAGS', 'Allow usage of tags')), @todo fix this to make it work.
			CheckboxField::create('AllowExport', _t('NewsSiteConfigExtension.ALLOWEXPORT', 'Allow content authors to export all data')), CheckboxField::create('AllowSlideshow', _t('NewsSiteConfigExtension.ALLOWSLIDESHOW', 'Allow the usage of the slideshow (in beta)'))
		);
	}

	/**
	 * Make sure the chosen action by the user is safe for usage.
	 */
	public function onBeforeWrite()
	{
		$maps = array(
			/** URL Mapping */
			'TagAction',
			'TagsAction',
			'ShowAction',
			'AuthorAction',
			'ArchiveAction',
		);
		foreach ($maps as $map) {
			if ($this->owner->$map) {
				$this->owner->$map = singleton('SiteTree')->generateURLSegment($this->owner->$map);
			}
		}
	}

	/**
	 * Returns the folder name where to store all news stuff relative to /assets/ directory.
	 *
	 * @return string
	 */
	public function getRootFolderName()
	{
		if ($this->owner->NewsRootFolderID) {
			return str_replace(ASSETS_DIR . '/', '', $this->owner->NewsRootFolder()->getRelativePath());
		}

		return Config::inst()->get($this->class, 'uploads_folder');
	}

	/**
	 * {@inheritdoc}
	 */
	public function populateDefaults()
	{
		// create a news folder
		$newsFolder = Folder::find_or_make(Config::inst()->get($this->class, 'uploads_folder'));
		$this->owner->NewsRootFolderID = $newsFolder->ID;
	}

}
