<?php
/**
 * Comment model.
 * Holds the comments for the selected news-item where it's posted.
 * Akismet options are optional. Can be set in _config to activate and add a key.
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 * @method News News() The origin of the comment
 */
class Comment extends DataObject implements PermissionProvider {

	/**
	 * Here are a bunch of statics. If you don't know what it does, you should read the Silverstripe documentation.
	 */
	private static $db = array(
		'Title'		=> 'Varchar(255)',
		'Name'		=> 'Varchar(255)',
		'Email'		=> 'Varchar(255)',
		'MD5Email'	=> 'Varchar(255)',
		'URL'		=> 'Varchar(255)',
		'Comment'	=> 'HTMLText',
		'AkismetMarked'	=> 'boolean(false)',
		'Visible'	=> 'boolean(true)',
		'ShowGravatar'	=> 'Boolean(true)',
	);

	private static $has_one = array(
		'News' => 'News',
	);

	private static $summary_fields = array(
		'Title',
		'Name',
		'Created',
		'AkismetMarked',
	);
	
	private static $default_sort = 'AkismetMarked ASC, Created DESC';

	/**
	 * Define singular name translatable
	 * @return type Singular name
	 */
	public function singular_name() {
		if (_t('Comment.SINGULARNAME')) {
			return _t('Comment.SINGULARNAME');
		} else {
			return parent::singular_name();
		} 
	}
	
	/**
	 * Define plural name translatable
	 * @return type Plural name
	 */
	public function plural_name() {
		if (_t('Comment.PLURALNAME')) {
			return _t('Comment.PLURALNAME');
		} else {
			return parent::plural_name();
		}   
	}
	
	/**
	 * Setup the fieldlabels and possibly the translations.
	 * @param boolean $includerelations
	 * @return array The array with (translated) fieldlabels.
	 */
	public function fieldLabels($includerelations = true) {
		$labels = parent::fieldLabels($includerelations);
		$commentLabels = array(
			'Title'		=> _t('Comment.TITLE', 'Title'),
			'Name'		=> _t('Comment.NAME', 'Name'),
			'Email'		=> _t('Comment.EMAIL', 'Email'),
			'URL'		=> _t('Comment.URL', 'URL'),
			'Comment'	=> _t('Comment.COMMENT', 'Comment'),
			'AkismetMarked'	=> _t('Comment.AKISMETMARKED', 'Akismet marked'),
			'Visible'	=> _t('Comment.VISIBLE', 'Visible'),
			'ShowGravatar'	=> _t('Comment.GRAVATAR', 'Show Gravatar'),
			'News'		=> _t('Comment.NEWS', 'News'),
		);
		return array_merge($commentLabels, $labels);
	}
	
	/**
	 * Setup the fields for the frontend
	 * @param type $params
	 * @return FieldList $fields the default FieldList
	 */
	public function getFrontEndFields($params = null) {
		$fields = parent::getFrontEndFields($params);
		$fields->removeByName(array(
			'MD5Email',
			'AkismetMarked',
			'Visible',
			'ShowGravatar',
			'News',
		));
		$fields->replaceField('Email', EmailField::create('Email', $this->fieldLabel('Email')));
		$fields->replaceField('Comment', TextAreaField::create('Comment', $this->fieldLabel('Comment')));
		$fields->fieldByName('Comment')
			->setColumns(20)
			->setRows(10);
		return $fields;
	}
	
	/** 
	 * If you hadn't guessed what the above does. Try the functions below!
	 */
	
	/**
	 * @return type FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('MD5Email');
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				TextField::create('Title', $this->fieldLabel('Title')),
				TextField::create('Name', $this->fieldLabel('Name')),
				TextField::create('Email', $this->fieldLabel('Email')),
				TextField::create('URL', $this->fieldLabel('URL')),
				HtmlEditorField::create('Comment', $this->fieldLabel('Comment')),
				CheckboxField::create('AkismetMarked', $this->fieldLabel('AkismetMarked')),
				CheckboxField::create('Visible', $this->fieldLabel('Visible')),
				CheckboxField::create('ShowGravatar', $this->fieldLabel('ShowGravatar')),
				// I very much doubt this is actually a good idea, to let authors change the item a comment is posted to :D
				DropdownField::create('NewsID', $this->fieldLabel('News'), News::get()->map('ID','Title'))
			)
		);
		return $fields;
	}
	
	/**
	 * Setup the visibility and check the URI, because ppl forget about it.
	 * Also, check Akismet.
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		$siteConfig = SiteConfig::current_site_config();
		if($siteConfig->MustApprove){
			$this->Visible = false;
		}
		/**
		 * No, I'm serious. Commenters forget that http is somewhat required to make the link actually work :'(
		 */
		if(substr($this->URL,0,4) != 'http' && $this->URL != ''){
			$this->URL = 'http://'.$this->URL;
		}
		/**
		 * For crying out loud, can't you just write the MD5 yours... Nevermind.
		 */
		$this->MD5Email = md5($this->Email);
		if($siteConfig->AkismetKey) {
			$this->checkAkismet($siteConfig);
		}
		/**
		 * PHP and HTML do not like each other I guess.
		 */
		$this->Comment = nl2br($this->Comment);
	}
	
	/**
	 * Setup the Gravatar, because handling from the template is messy.
	 * @return string $link The link to the Gravatar-file.
	 */
	public function getGravatar(){
		$siteConfig = SiteConfig::current_site_config();
		$default = '';
		$gravatarSize = '32';
		if($siteConfig->DefaultGravatarImageID != 0){
			$default = urlencode(Director::absoluteBaseURL().$siteConfig->DefaultGravatarImage()->Link());
		}
		elseif($siteConfig->DefaultGravatar != ''){
			$default = urlencode($siteConfig->DefaultGravatar);
		}
		if($siteConfig->GravatarSize){
			$gravatarSize = $siteConfig->GravatarSize;
		}
		return 'http://www.gravatar.com/avatar/$MD5Email?default='.$default.'&amp;s='.$gravatarSize;
	}
	
	/**
	 * I would actually advice to change a few things here, personally.
	 */
	public function onAfterWrite(){
		$SiteConfig = SiteConfig::current_site_config();
		/** No, really, I mean it. Change this. When spambots find your site, 30 e-mails an hour is NORMAL! */
		$mail = Email::create();
		$mail->setTo($SiteConfig->NewsEmail);
		$mail->setSubject(_t('Comment.COMMENTMAILSUBJECT2', 'New post titled: {title} ', array('title' => $this->Title)));
		$mail->setFrom($this->Email);
		$mail->setTemplate('CommentPost');
		$mail->populateTemplate($this);
		$mail->send();
	}
	
	/**
	 * If we have Akismet configured, check if this comment should be marked as spam.
	 * Or ham. Or bacon. Or steak! Steak would be good!
	 * @param SiteConfig $siteConfig
	 */
	private function checkAkismet(SiteConfig $siteConfig) {
		try {
			$akismet = new Akismet(Director::absoluteBaseURL(), $siteConfig->AkismetKey);
			$akismet->setCommentAuthor($this->Name);
			$akismet->setCommentContent($this->Comment);
			$akismet->setCommentAuthorEmail($this->Email);
			$akismet->setCommentAuthorURL($this->URL);
			$result = (int)$akismet->isCommentSpam();
			if($result){
				$this->AkismetMarked = true;
				$this->Visible = false;
			}

		} catch (Exception $e) {
			/**
			 * Akismet didn't work, most likely the service is down.
			 * Just to be on the safe side, we hide this comment.
			 */
			$this->Visible = false;
		}
	}
	
	/**
	 * Permissions.
	 * Because ehm... Well. You know.
	 */
	public function providePermissions() {
		return array(
			'CREATE_COMMENT' => array(
				'name' => _t('News.PERMISSION_CREATE_DESCRIPTION', 'Create comments'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_CREATE_HELP', 'Permission required to create new comments in the CMS.')
			),
			'EDIT_COMMENT' => array(
				'name' => _t('News.PERMISSION_EDIT_DESCRIPTION', 'Edit comments'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_EDIT_HELP', 'Permission required to edit existing comments.')
			),
			'DELETE_COMMENT' => array(
				'name' => _t('News.PERMISSION_DELETE_DESCRIPTION', 'Delete comments'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_DELETE_HELP', 'Permission required to delete existing comments.')
			),
			'VIEW_COMMENT' => array(
				'name' => _t('News.PERMISSION_VIEW_DESCRIPTION', 'View comments'),
				'category' => _t('Permissions.CONTENT_CATEGORY', 'Content permissions'),
				'help' => _t('News.PERMISSION_VIEW_HELP', 'Permission required to view existing comments in the CMS.')
			),
		);
	}
	
	public function canCreate($member = null) {
		return(Permission::checkMember($member, array('CREATE_COMMENT', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canEdit($member = null) {
		return(Permission::checkMember($member, array('EDIT_COMMENT', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canDelete($member = null) {
		return(Permission::checkMember($member, array('DELETE_COMMENT', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canView($member = null) {
		return(Permission::checkMember($member, array('VIEW_COMMENT', 'CMS_ACCESS_NewsAdmin')));
	}

}
