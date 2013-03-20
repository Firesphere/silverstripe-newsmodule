<?php
/**
 * Add some settings to the siteconfig. Pretty easy, right?
 *
 * TweetOnPost requires a new module I'm busy writing. To connect Silverstripe admin to Twitter.
 * @package News/blog module
 * @author Sphere
 */
class NewsSiteConfigDecorator extends DataExtension {

	public static $db = array(
		'Comments' => 'boolean(true)',
		'MustApprove' => 'boolean(true)',
		'Gravatar' => 'boolean(true)',
		'AkismetKey' => 'Varchar(255)',
		'ExtraSecurity' => 'Boolean(true)',
		'PostsPerPage' => 'Int',
		'DefaultGravatar' => 'Varchar(255)',
		'GravatarSize' => 'Int',
		'TweetOnPost' => 'Boolean(false)',
		/**
		 * Preparing the slideshow integration!
		 */
		'SlideshowInitial' => 'Boolean(true)',
		'SlideshowSize' => 'Varchar(15)',
	);
	
	public function updateCMSFields(FieldList $fields){

		$fields->addFieldToTab(
			'Root',
			TabSet::create(
				'Newssettings', // name
				_t($this->class . '.NEWSCOMMENTS', 'News settings'), // title
				Tab::create(
					'News',
					_t($this->class . '.NEWS','News'),
					CheckboxField::create('TweetOnPost', _t($this->class . '.TWEETPOST', 'Tweet na posten?')),
					TextField::create('PostsPerPage', _t($this->class . '.PPP', 'Amount of posts per page'))
				),
				Tab::create(
					'Comments',
					_t($this->class . '.COMMENTSSETTINGS', 'Comments'),
					CheckboxField::create('Comments', _t($this->class . '.COMMENTS', 'Allow comments on newsitems')),
					CheckboxField::create('MustApprove', _t($this->class . '.APPROVE', 'Comments must be approved')),
					CheckboxField::create('Gravatar', _t($this->class . '.GRAVATAR', 'Use Gravatar-Image')),
					TextField::create('DefaultGravatar', _t($this->class . '.GRAVURL', 'Default Gravatar-image url')),
					NumericField::create('GravatarSize', _t($this->class . '.GRAVSIZE', 'Gravatar image size')),
					TextField::create('AkismetKey', _t($this->class . '.AKISMET', 'Akismet API key')),
					CheckboxField::create('ExtraSecurity', _t($this->class . '.SPAMPROTECTION', 'Use an extra field for spamprotection'))
				),
				Tab::create(
					'Slideshowsettings', // name
					_t($this->class . '.SLIDESHOWSETTINGS', 'Slideshow'), // title
					CheckboxField::create('SlideshowInitial', _t($this->class . '.SLIDEINITIAL', 'Show only the first image, the rest will have css-class hidden.')),
					TextField::create('SlideshowSize', _t($this->class . '.SLIDESIZE', 'Size of the images. Leave blank or 0 to control from CSS'))
				)
			),
			'Access'
		);
	}
}
