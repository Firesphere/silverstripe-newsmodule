<?php
/**
 * Handle renamed topics.
 * When a post is being renamed, it's URLSegment is updated.
 * To prevent bookmarks and Spiders to get completely lost, we store the old link and redirect according to the news which it belongs to.
 * This functionality is chosen in favor of fully keeping a history of all the news.
 * 
 * @package News/Blog module
 * @author Sphere
 */
class Renamed extends DataObject{
    
	public static $db = array(
		'OldLink' => 'Varchar(255)'
	);

	public static $has_one = array(
		'News' => 'News',
	);

	public static $indexes = array(
		'OldLink' => true
	);
	
}
