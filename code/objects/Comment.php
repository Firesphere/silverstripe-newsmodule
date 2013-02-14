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
		'Visible' => 'boolean(true)'
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
		if((substr($this->URL,0,7)!='http://' || substr($this->URL,0,8)!='https://') && $this->URL != ''){
			$this->URL = 'http://'.$this->URL;
		}
		$this->MD5Comment = md5($this->Email);
		if(SSAkismet::isEnabled()) {
			try {
				$akismet = new SSAkismet();
				$akismet->setCommentAuthor($this->Name);
				$akismet->setCommentContent($this->Comment);

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
