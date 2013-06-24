<?php
/**
 * Add some settings to the siteconfig. Pretty easy, right?
 *
 * @package News/blog module
 * @author Sphere
 */
class NewsSiteConfigExtension extends DataExtension {

	/**
	 * @var type array of all the extra's we need for setting everything up.
	 */
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
		'ExtraSecurity' => 'Boolean(true)',
		/** External options */
		'AllowExternals' => 'Boolean(true)',
		'AllowDownloads' => 'Boolean(true)',
		'ReturnExternal' => 'Boolean(true)',
		/** Security settings */
		'AllowAuthors' => 'Boolean(false)',
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
					'Newssettings', // name
					_t($this->class . '.NEWSCOMMENTS', 'News settings'), // title
					/** General news settings */
					Tab::create(
						'News', // Name
						_t($this->class . '.NEWS','News'), // Title
						CheckboxField::create('UseAbstract', _t($this->class . '.ABSTRACT', 'Use abstract/summary')),
						CheckboxField::create('TweetOnPost', _t($this->class . '.TWEETPOST', 'Tweet after posting?')), // Requires Firesphere/silverstripe-social
						NumericField::create('PostsPerPage', _t($this->class . '.PPP', 'Amount of posts per page'))
					),
					/** External linking options */
					Tab::create(
						'External',
						_t($this->class . '.EXTERNAL', 'External linking'),
						CheckboxField::create('AllowExternals', _t($this->class . '.ALLOWEXT', 'Allow linking to external articles')),
						CheckboxField::create('AllowDownloads', _t($this->class . '.ALLOWDOWN', 'Allow linking to downloads')),
						CheckboxField::create('ReturnExternal', _t($this->class . '.EXTERNAL', 'Make externals open in a new tab/window'))
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
						NumericField::create('GravatarSize', _t($this->class . '.GRAVSIZE', 'Gravatar image size (32 for 32x32px)')),
						TextField::create('AkismetKey', _t($this->class . '.AKISMET', 'Akismet API key')),
						CheckboxField::create('ExtraSecurity', _t($this->class . '.SPAMPROTECTION', 'Use an extra field for spamprotection')),
						CheckboxField::create('NoscriptSecurity', _t($this->class . '.NOSCRIPTSPAM', 'Use a noscript field for spamprotection'))
					),
					/** Slideshow settings */
					Tab::create(
						'Slideshowsettings', // name
						_t($this->class . '.SLIDESHOWSETTINGS', 'Slideshow'), // title
						CheckboxField::create('EnableSlideshow', _t($this->class . '.SLIDESHOW', 'Allow the use of slideshow feature')),
						CheckboxField::create('SlideshowInitial', _t($this->class . '.SLIDEINITIAL', 'Show only the first image, the rest will have css-class hidden.')),
						TextField::create('SlideshowSize', _t($this->class . '.SLIDESIZE', 'Size of the images. Leave blank or 0 to control from CSS'))
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
						CheckboxField::create('AllowAuthors', _t($this->class . '.ALLOWAUTHOR', 'Allow Content Authors to edit the newsconfiguration'))
					)
				);
			}
		}
	}
}
