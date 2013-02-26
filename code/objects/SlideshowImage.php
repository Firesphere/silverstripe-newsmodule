<?php
/**
 * Slideshow Image is the holder for the images for the slideshow.
 * This is unfinished businees, I'm still not entirely done thinking this over.
 * Experimental implementation.
 *
 * @package News/Blog module
 * @author Simon 'Sphere' Erkelens
 */
class SlideshowImage extends DataObject {
	
	public static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'HTMLText',
		'SortOrder' => 'Int',
	);
	
	public static $has_one = array(
		'Image' => 'Image',
		'News' => 'News',
	);
	
	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields($params);
		$fields->removeFieldsFromTab('Root.Main', array('Image','News','Sortorder'));
		return $fields;
	}

}
