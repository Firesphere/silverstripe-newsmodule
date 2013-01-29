<?php
/**
 * Add some settings to the siteconfig. Pretty easy, right?
 *
 * @package News/blog module
 * @author Sphere
 */
class NewsSiteConfigDecorator extends DataExtension {

	public static $db = array(
		'Comments' => 'boolean(true)',
		'MustApprove' => 'boolean(true)',
		'Gravatar' => 'boolean(true)',
		'AkismetKey' => 'Varchar(255)',
		'PostsPerPage' => 'Int',
		'DefaultGravatar' => 'Varchar(255)',
		'GravatarSize' => 'Int',
		'TweetOnPost' => 'Boolean(false)',
	);
	
	public function updateCMSFields(FieldList $fields){

		$fields->addFieldToTab(
			'Root',
			Tab::create(
				'Newssettings', // name
				_t($this->class . '.NEWSCOMMENTS', 'News settings'), // title
				CheckboxField::create('Comments', _t($this->class . '.COMMENTS', 'Allow comments on newsitems')),
				CheckboxField::create('MustApprove', _t($this->class . '.APPROVE', 'Comments must be approved')),
				CheckboxField::create('TweetOnPost', _t($this->class . '.TWEETPOST', 'Tweet na posten?')),
				CheckboxField::create('Gravatar', _t($this->class . '.GRAVATAR', 'Use Gravatar-Image')),
				TextField::create('DefaultGravatar', _t($this->class . '.GRAVURL', 'Default Gravatar-image url')),
				NumericField::create('GravatarSize', _t($this->class . '.GRAVSIZE', 'Gravatar image size')),
				TextField::create('AkismetKey', _t($this->class . '.AKISMET', 'Akismet API key')),
				TextField::create('PostsPerPage', _t($this->class . '.PPP', 'Amount of posts per page'))
			),
			'Access'
		);
	}
}
