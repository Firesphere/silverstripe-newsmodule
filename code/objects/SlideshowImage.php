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
	
	/**
	 * @todo built a good fieldlist!
	 */
	public function getCMSFields($params = null) {
		$fields = parent::getCMSFields($params);
		$fields->removeFieldsFromTab('Root.Main', array('NewsID','SortOrder'));
		return $fields;
	}
	
	/**
	 * If there's a max-size set in the SiteConfig ({width}x{height}) for the image, resize the image.
	 * This saves space on the hosting and prevents huge high-res images to stay online for no reason.
	 */
	public function onAfterWrite(){
		parent::onAfterWrite();
		$SiteConfig = SiteConfig::current_site_config();
		if($SiteConfig->SlideshowSize){
			$splitter = trim(str_replace(range(0,9),'',$SiteConfig->SlideshowSize));
			$size = explode($splitter, $SiteConfig->SlideshowSize);
			if ($this->Image()->getWidth() > $size[0]){
				$maxSized = $this->Image()->SetWidth($size[0]);

				unlink(Director::baseFolder() . '/' . $this->Image()->getFilename());
				copy(Director::baseFolder() . '/' . $maxSized->getFilename(), Director::baseFolder() . '/' . $this->Image()->getFilename());
				unlink(Director::baseFolder() . '/' . $maxSized->getFilename());
			}
			if ($this->Image()->getHeight() > $size[1]){
				$maxSized = $this->Image()->SetHeight($size[1]);

				unlink(Director::baseFolder() . '/' . $this->Image()->getFilename());
				copy(Director::baseFolder() . '/' . $maxSized->getFilename(), Director::baseFolder() . '/' . $this->Image()->getFilename());
				unlink(Director::baseFolder() . '/' . $maxSized->getFilename());
			}
		}
	}

}
