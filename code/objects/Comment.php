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
class Comment extends DataObject {

	/**
	 * Here are a bunch of statics. If you don't know what it does, you should read the Silverstripe documentation.
	 */
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Name' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'MD5Email' => 'Varchar(255)',
		'URL' => 'Varchar(255)',
		'Comment' => 'HTMLText',
		'AkismetMarked' => 'boolean(false)',
		'Visible' => 'boolean(true)',
		'ShowGravatar' => 'Boolean(true)',
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
	/** If you hadn't guessed what the above does. Try the functions below! */
	
	/**
	 * For translations, we need a few updates here, but at least we hide the md5 of the e-mail.
	 * @return type FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('MD5Email');
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				TextField::create('Title', _t('Comment.TITLE', 'Title')),
				TextField::create('Name', _t('Comment.NAME', 'Name')),
				TextField::create('Email', _t('Comment.EMAIL', 'Email')),
				TextField::create('URL', _t('Comment.URL', 'URL')),
				HtmlEditorField::create('Comment', _t('Comment.COMMENT', 'Comment')),
				CheckboxField::create('AkismetMarked', _t('Comment.AKISMETMARKED', 'Akismet marked')),
				CheckboxField::create('Visible', _t('Comment.VISIBLE', 'Visible')),
				CheckboxField::create('ShowGravatar', _t('Comment.GRAVATAR', 'Show Gravatar')),
				// I very much doubt this is actually a good idea, to let authors change the item a comment is posted to :D
				DropdownField::create('NewsID', _t('Comment.NEWS', 'News'), News::get()->map('ID','Title'))
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
		$SiteConfig = SiteConfig::current_site_config();
		if($SiteConfig->MustApprove){
			$this->Visible = false;
		}
		else{
			$this->Visible = true;
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
		if($SiteConfig->AkismetKey) {
			$this->checkAkismet();
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
		$SiteConfig = SiteConfig::current_site_config();
		if($SiteConfig->DefaultGravatarImageID != 0){
			$default = urlencode(Director::absoluteBaseURL().$SiteConfig->DefaultGravatarImage()->Link());
		}
		elseif($SiteConfig->DefaultGravatar != ''){
			$default = urlencode($SiteConfig->DefaultGravatar);
		}
		else{
			$default = '';
		}
		if($SiteConfig->GravatarSize){
			$GravatarSize = $SiteConfig->GravatarSize;
		}
		else{
			$GravatarSize = '32';
		}
		$link = 'http://www.gravatar.com/avatar/$MD5Email?default='.$default.'&amp;s='.$GravatarSize;
		return $link;
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
	
	private function checkAkismet() {
		try {
			$akismet = new Akismet(Director::absoluteBaseURL(), $SiteConfig->AkismetKey);
			$akismet->setCommentAuthor($this->Name);
			$akismet->setCommentContent($this->Comment);
			$akismet->setCommentAuthorEmail($this->Email);
			$akismet->setCommentAuthorURL($this->URL);
			$result = (int)$akismet->isCommentSpam();
			if($result){
				$this->AkismetMarked = true;
			}

		} catch (Exception $e) {
			// Akismet didn't work, most likely the service is down.
			// Suggested options:
			// Do absolutely nothing
			// $this->Visible = false;
		}
	}
	
	/**
	 * Permissions.
	 * Because ehm... Well. You know.
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
		return true;
	}

}
