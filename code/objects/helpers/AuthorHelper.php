<?php
/**
 * Collect the authors for author-specific pages.
 * NOT needed for users to edit, thus NOT in the admin.
 *
 * @package News/blog module
 * @author Simon 'Sphere' Erkelens
 * @method News NewsItems() The linked Newsitems to this author
 */
class AuthorHelper extends DataObject {
	
	private static $db = array(
		'OriginalName' => 'Varchar(255)',
		'URLSegment' => 'Varchar(255)',
	);
	
	private static $has_many = array(
		'NewsItems' => 'News',
	);
	
	private static $indexes = array(
		'URLSegment' => true,
	);
	
	public function onBeforeWrite()	{
		parent::onBeforeWrite();
		$this->OriginalName = trim($this->OriginalName);
		if(!$this->URLSegment){
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->OriginalName);
		}
	}

	/**
	 * Free guess on what this button does.
	 * @return string Link to this object.
	 */
	public function Link($action = 'author/') {
		if($siteConfigAction = SiteConfig::current_site_config()->AuthorAction) {
			$action = $siteConfigAction;
		}
		if ($Page = $this->NewsHolderPages()->first()) {
			return($Page->Link($action.'/'.$this->URLSegment));
		}
		return false;
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param string $action The added URLSegment, the actual function that'll return the news.
	 * @return string Link. To the item. (Yeah, I'm super cereal here)
	 */
	public function AbsoluteLink(){
		if($Page = $this->Link()){
			return(Director::absoluteURL($Page));
		}		
	}
}
