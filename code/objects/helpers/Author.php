<?php
/**
 * Collect the authors for author-specific pages.
 * NOT needed for users to edit, thus NOT in the admin.
 *
 * @package News/blog module
 * @author Simon 'Sphere' Erkelens
 */
class Author extends DataObject {
	
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
		if(!$this->URLSegment){
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->OriginalName);
		}
	}

}
