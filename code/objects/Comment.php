<?php
/**
 * Comment model.
 * Holds the comments for the selected news-item where it's posted.
 * Akismet options are optional. Can be set in _config to activate and add a key.
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 */
class Comment extends DataObject {

	public static $db = array(
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

	public static $has_one = array(
		'News' => 'News',
	);

	public static $summary_fields = array(
		'Title',
		'Name',
		'Created',
		'AkismetMarked',
	);

	/**
	 * Define singular name translatable
	 * @return type Singular name
	 */
	public function singular_name() {
		if (_t($this->class . '.SINGULARNAME')) {
			return _t($this->class . '.SINGULARNAME');
		} else {
			return parent::singular_name();
		} 
	}
	
	/**
	 * Define plural name translatable
	 * @return type Plural name
	 */
	public function plural_name() {
		if (_t($this->class . '.PLURALNAME')) {
			return _t($this->class . '.PLURALNAME');
		} else {
			return parent::plural_name();
		}   
	}
	
	/**
	 * For translations, we need a few updates here, but at least we hide the md5 of the e-mail.
	 * @return type FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('MD5Email');
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
		if(substr($this->URL,0,4) != 'http' && $this->URL != ''){
			$this->URL = 'http://'.$this->URL;
		}
		$this->MD5Email = md5($this->Email);
		if(class_exists(Akismet) && $SiteConfig->AkismetKey) {
			try {
				$akismet = new Akismet(Director::absoluteBaseURL(), $SiteConfig->AkismetKey);
				$akismet->setCommentAuthor($this->Name);
				$akismet->setCommentContent($this->Comment);
				$akismet->setCommentAuthorEmail($this->Email);
				$akismet->setCommentAuthorURL($this->URL);
				$result = (int)$akismet->isCommentSpam();
				if($result){
					if(SSAkismet::getSaveSpam()) $this->AkismetMarked = true;
				}

			} catch (Exception $e) {
				// Akismet didn't work, most likely the service is down.
				// Suggested options:
				// Do absolutely nothing
				// $this->Visible = false;
			}
		}
		$this->Comment = nl2br($this->Comment);
	}
	
	/**
	 * I would actually advice to change a few things here, personally.
	 * Examples:
	 * Add an info e-mail field to your siteconfig, and use that field as your reference for the setTo.
	 * Also, the From could be $this->Email, so you could reply if needed. (Unless you decided E-mail is not required)
	 * Besides that, you could add an if-method to only e-mail when a comment is marked by Akismet. 
	 */
	public function onAfterWrite(){
		$SiteConfig = SiteConfig::current_site_config();
		if($this->AkismetMarked == true || $SiteConfig->MustApprove == true){
			$mail = new Email();
			$mail->setTo('you@your-domain.com');
			$mail->setSubject('New post titled: ' .$this->Title);
			$mail->setFrom('info@your-domain.com');
			$mail->setTemplate('CommentPost');
			$mail->populateTemplate($this);
			$mail->send();
		}
	}
	
}
