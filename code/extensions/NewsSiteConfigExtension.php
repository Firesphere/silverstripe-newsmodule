<?php
/**
 * Add some settings to the siteconfig. Pretty easy, right?
 *
 * @package News/blog module
 * @author Sphere
 */
class NewsSiteConfigExtension extends DataExtension {

	/** @var array $db Contains all the extra's we need for setting everything up. */
	private static $db = array(
		/** Default options */
		'UseAbstract' => 'Boolean(true)',
		'PostsPerPage' => 'Int',
		'TweetOnPost' => 'Boolean(false)',
		/** Slideshow options */
		'EnableSlideshow' => 'Boolean(true)',
		'SlideshowInitial' => 'Boolean(true)',
		'SlideshowSize' => 'Varchar(15)',
		/** Comment options */
		'Comments' => 'boolean(true)',
		'NewsEmail' => 'Varchar(255)',
		'MustApprove' => 'boolean(true)',
		'Gravatar' => 'boolean(true)',
		'DefaultGravatar' => 'Varchar(255)',
		'GravatarSize' => 'Int',
		'AkismetKey' => 'Varchar(255)',
		'NoscriptSecurity' => 'Boolean(true)',
		'ExtraSecurity' => 'Boolean(true)',
		/** External options */
		'AllowExternals' => 'Boolean(true)',
		'AllowDownloads' => 'Boolean(true)',
		'ReturnExternal' => 'Boolean(true)',
		/** Security settings */
		'AllowAuthors' => 'Boolean(false)',
		'AllowTags' => 'Boolean(true)',
		'AllowExport' => 'Boolean(false)',
		'AllowSlideshow' => 'Boolean(true)',
		/** Social data */
		'TwitterAccount' => 'Varchar(255)',
	);
	
	/** @var array $has_one Contains all the one-to-many relations */
	private static $has_one = array(
		'DefaultImage' => 'Image',
		'DefaultGravatarImage' => 'Image',
	);
	
	/**
	 * Update the SiteConfig with the news-settings.
	 * @param FieldList $fields of current FieldList of SiteConfig
	 */
	public function updateCMSFields(FieldList $fields){
		if(($this->owner->AllowAuthors && Member::currentUser()->inGroup('content-authors')) || Member::currentUser()->inGroup('administrators')){
			$fields->addFieldToTab(
				'Root', // What tab
				TabSet::create(
					'Newssettings',
					_t('NewsSiteConfigExtension.NEWSCOMMENTS', 'News settings'),
					/** General news settings */
					Tab::create(
						'News',
						_t('NewsSiteConfigExtension.NEWS','News'),
						CheckboxField::create('UseAbstract', _t('NewsSiteConfigExtension.ABSTRACT', 'Use abstract/summary')),
						CheckboxField::create('TweetOnPost', _t('NewsSiteConfigExtension.TWEETPOST', 'Tweet after posting a new item')), // Requires Firesphere/silverstripe-social
						NumericField::create('PostsPerPage', _t('NewsSiteConfigExtension.PPP', 'Amount of posts per page')),
						UploadField::create('DefaultImage', _t('NewsSiteConfigExtension.DEFAULTIMPRESSION', 'Default impression image for newsitems'))
					),
					/** External linking options */
					Tab::create(
						'External',
						_t('NewsSiteConfigExtension.EXTERNAL', 'External linking'),
						CheckboxField::create('AllowExternals', _t('NewsSiteConfigExtension.ALLOWEXT', 'Allow external links')),
						CheckboxField::create('AllowDownloads', _t('NewsSiteConfigExtension.ALLOWDOWN', 'Allow downloads')),
						CheckboxField::create('ReturnExternal', _t('NewsSiteConfigExtension.RETURNEXT', 'Make externals open in a new tab/window'))
					),
					/** Comment settings */
					Tab::create(
						'Comments',
						_t('NewsSiteConfigExtension.COMMENTSSETTINGS', 'Comments'),
						CheckboxField::create('Comments', _t('NewsSiteConfigExtension.COMMENTS', 'Allow comments on items')),
						CheckboxField::create('MustApprove', _t('NewsSiteConfigExtension.APPROVE', 'Comments must be approved')),
						EmailField::create('NewsEmail', _t('NewsSiteConfigExtension.NEWSMAIL', 'Send email notification of a comment to me')),
						CheckboxField::create('Gravatar', _t('NewsSiteConfigExtension.GRAVATAR', 'Display Gravatar image of commenter')),
						TextField::create('DefaultGravatar', _t('NewsSiteConfigExtension.GRAVURL', 'Default Gravatar image if commenter doesn\'t have one')),
						UploadField::create('DefaultGravatarImage', _t('NewsSiteConfigExtension.UPLOADGRAVATAR', 'Or upload a default Gravatar image')),
						NumericField::create('GravatarSize', _t('NewsSiteConfigExtension.GRAVSIZE', 'Size of the Gravatar image (e.g. 32 for a 32x32 image)')),
						TextField::create('AkismetKey', _t('NewsSiteConfigExtension.AKISMET', 'Akismet API key')),
						CheckboxField::create('ExtraSecurity', _t('NewsSiteConfigExtension.SPAMPROTECTION', 'Use an extra field for spam protection')),
						CheckboxField::create('NoscriptSecurity', _t('NewsSiteConfigExtension.NOSCRIPTSPAM', 'Use a noscript field for spam protection'))
					),
					/** Slideshow settings */
					Tab::create(
						'Slideshowsettings',
						_t('NewsSiteConfigExtension.SLIDESHOWSETTINGS', 'Slideshow'),
						CheckboxField::create('EnableSlideshow', _t('NewsSiteConfigExtension.SLIDESHOW', 'Allow the use of slideshow feature')),
						CheckboxField::create('SlideshowInitial', _t('NewsSiteConfigExtension.SLIDEINITIAL', 'Show only the first image')),
						TextField::create('SlideshowSize', _t('NewsSiteConfigExtension.SLIDESIZE', 'Maximum size of the full-size images. E.g. 1024x768'))
					),
					Tab::create(
						'Help',
						_t('NewsSiteConfigExtension.HELP', 'Help'),
						ReadonlyField::create('generalhelp', _t('NewsSiteConfigExtension.NEWSHELP', 'News help'), _t('NewsSiteConfigExtension.NEWSHELPTEXT', 'In the news settings tab, you can set general settings like if you want to use an abstract, tweet after post (this is on the issuelist!) Fields are quite understandable by itself.')),
						ReadonlyField::create('externalhelp', _t('NewsSiteConfigExtension.EXTERNALHELP', 'External help'), _t('NewsSiteConfigExtension.EXTERNALHELPTEXT', 'Allow or disallow content authors to link to external items and set how to handle external items. Open a new tab/window or open in the same tab/window.')),
						ReadonlyField::create('commenthelp', _t('NewsSiteConfigExtension.COMMENTHELP', 'Comment help'), _t('NewsSiteConfigExtension.COMMENTHELPTEXT', 'Comment help is tbd.')),
						ReadonlyField::create('slideshowhelp', _t('NewsSiteConfigExtension.SLIDESHOWHELP', 'Slideshow help'), _t('NewsSiteConfigExtension.SLIDESHOWHELPTEXT', 'Slideshow settings, like what to do. TBD'))
					)
				),
				'Access'
			);
			if(Member::currentUser()->inGroup('administrators')){
				$fields->addFieldToTab(
					'Root.Newssettings',
					Tab::create(
						'Security',
						_t('NewsSiteConfigExtension.SEC', 'Security'),
						CheckboxField::create('AllowAuthors', _t('NewsSiteConfigExtension.ALLOWAUTHOR', 'Allow content authors to edit news settings')),
//						CheckboxField::create('AllowTags', _t('NewsSiteConfigExtension.ALLOWTAGS', 'Allow usage of tags')), @todo fix this to make it work.
						CheckboxField::create('AllowExport', _t('NewsSiteConfigExtension.ALLOWEXPORT', 'Allow content authors to export all data')),
						CheckboxField::create('AllowSlideshow', _t('NewsSiteConfigExtension.ALLOWSLIDESHOW', 'Allow the usage of the slideshow (in beta)'))
					),
					'Help'
				);
			}
		}
	}
}
