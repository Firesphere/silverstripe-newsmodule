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
					_t($this->class . '.NEWSCOMMENTS', 'News settings'),
					/** General news settings */
					Tab::create(
						'News',
						_t($this->class . '.NEWS','News'),
						CheckboxField::create('UseAbstract', _t($this->class . '.ABSTRACT', 'Use abstract/summary')),
						CheckboxField::create('TweetOnPost', _t($this->class . '.TWEETPOST', 'Tweet after posting?')), // Requires Firesphere/silverstripe-social
						NumericField::create('PostsPerPage', _t($this->class . '.PPP', 'Amount of posts per page')),
						UploadField::create('DefaultImage', _t($this->class . '.DEFAULTIMPRESSION', 'Default Impressionimage'))
					),
					/** External linking options */
					Tab::create(
						'External',
						_t($this->class . '.EXTERNAL', 'External linking'),
						CheckboxField::create('AllowExternals', _t($this->class . '.ALLOWEXT', 'Allow linking to external articles')),
						CheckboxField::create('AllowDownloads', _t($this->class . '.ALLOWDOWN', 'Allow linking to downloads')),
						CheckboxField::create('ReturnExternal', _t($this->class . '.RETURNEXT', 'Make externals open in a new tab/window'))
					),
					/** Comment settings */
					Tab::create(
						'Comments',
						_t($this->class . '.COMMENTSSETTINGS', 'Comments'),
						CheckboxField::create('Comments', _t($this->class . '.COMMENTS', 'Allow comments on newsitems')),
						CheckboxField::create('MustApprove', _t($this->class . '.APPROVE', 'Comments must be approved')),
						EmailField::create('NewsEmail', _t($this->class . '.NEWSMAIL', 'Send e-mailnotification of a comment to me')),
						CheckboxField::create('Gravatar', _t($this->class . '.GRAVATAR', 'Use Gravatar-Image')),
						TextField::create('DefaultGravatar', _t($this->class . '.GRAVURL', 'Default Gravatar-image url')),
						UploadField::create('DefaultGravatarImage', _t($this->class . '.UPLOADGRAVATAR', 'Or upload a default Gravatar image')),
						NumericField::create('GravatarSize', _t($this->class . '.GRAVSIZE', 'Gravatar image size (32 for 32x32px)')),
						TextField::create('AkismetKey', _t($this->class . '.AKISMET', 'Akismet API key')),
						CheckboxField::create('ExtraSecurity', _t($this->class . '.SPAMPROTECTION', 'Use an extra field for spamprotection')),
						CheckboxField::create('NoscriptSecurity', _t($this->class . '.NOSCRIPTSPAM', 'Use a noscript field for spamprotection'))
					),
					/** Slideshow settings */
					Tab::create(
						'Slideshowsettings',
						_t($this->class . '.SLIDESHOWSETTINGS', 'Slideshow'),
						CheckboxField::create('EnableSlideshow', _t($this->class . '.SLIDESHOW', 'Allow the use of slideshow feature')),
						CheckboxField::create('SlideshowInitial', _t($this->class . '.SLIDEINITIAL', 'Show only the first image, the rest will have css-class hidden.')),
						TextField::create('SlideshowSize', _t($this->class . '.SLIDESIZE', 'Size of the images. Leave blank or 0 to control from CSS'))
					),
					Tab::create(
						'Help',
						_t($this->class . '.HELP', 'Help'),
						ReadonlyField::create('generalhelp', _t($this->class . '.NEWSHELP', 'News help'), _t($this->class . '.NEWSHELPTEXT', 'In the news-settings tab, you can set general settings like if you want to use an abstract, tweet after post (this is on the issuelist!) Fields are quite understandable by itself.')),
						ReadonlyField::create('externalhelp', _t($this->class . '.EXTERNALHELP', 'External help'), _t($this->class . '.EXTERNALHELPTEXT', 'Allow or disallow content-authors to link to external items and set how to handle external items. Open a new tab/window or open in the same tab/window.')),
						ReadonlyField::create('commenthelp', _t($this->class . '.COMMENTHELP', 'Comment help'), _t($this->class . '.COMMENTHELPTEXT', 'Comment help is tbd.')),
						ReadonlyField::create('slideshowhelp', _t($this->class . '.SLIDESHOWHELP', 'Slideshow help'), _t($this->class . '.SLIDESHOWHELPTEXT', 'Slideshow settings, like what to do. TBD'))
					)
				),
				'Access'
			);
			if(Member::currentUser()->inGroup('administrators')){
				$fields->addFieldToTab(
					'Root.Newssettings',
					Tab::create(
						'Security',
						_t($this->class . '.SEC', 'Security'),
						CheckboxField::create('AllowAuthors', _t($this->class . '.ALLOWAUTHOR', 'Allow Content Authors to edit the newsconfiguration')),
//						CheckboxField::create('AllowTags', _t($this->class . '.ALLOWTAGS', 'Allow usage of tags')), @todo fix this to make it work.
						CheckboxField::create('AllowExport', _t($this->class . '.ALLOWEXPORT', 'Allow exporting of items')),
						CheckboxField::create('AllowSlideshow', _t($this->class . '.ALLOWSLIDESHOW', 'Allow the usage of the slideshow-feature'))
					),
					'Help'
				);
			}
		}
	}
}
