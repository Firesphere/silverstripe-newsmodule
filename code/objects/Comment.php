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
		'MD5Comment' => 'Varchar(255)',
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
	 * Setup the visibility and check the URI, because ppl forget about it.
	 * Also, check Akismet.
	 * 
	 * @todo Send e-mail when new comment is posted and awaiting approval OR is marked as spam. 
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
			}
		}
	}
}
