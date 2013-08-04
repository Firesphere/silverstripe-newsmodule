<?php
/**
 * Slideshow Image is the holder for the images for the slideshow.
 *
 * @package News/Blog module
 * @author Simon `Sphere`
 * @method Image Image Image for this group
 * @method News News this image belongs to
 */
class SlideshowImage extends DataObject {

	/** @var array $db */
	private static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'HTMLText',
		'SortOrder' => 'Int',
	);
	
	/** @var array $has_one */
	private static $has_one = array(
		'Image' => 'Image',
		'News' => 'News',
	);

	/**
	 * Setup the CMSFields
	 * @return FieldList $fields Fields to be shown in the admin.
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeFieldsFromTab('Root.Main', array('NewsID','SortOrder'));
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				HtmlEditorField::create('Description', _t($this->class . '.DESCRIPTION', 'Description')),
				UploadField::create('Image', _t($this->class . '.IMAGE', 'Image')),
				TextField::create('Title', _t($this->class . '.TITLE', 'Title'))
			)
		);
		return $fields;
	}
	
	/**
	 * If there's a max-size set in the SiteConfig ({width}x{height}) for the image, resize the image.
	 * This saves space on the hosting and prevents huge high-res images to stay online for no reason.
	 */
	public function onAfterWrite(){
		parent::onAfterWrite();
		/** Limit uploaded images to the setting in the siteconfig. */
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
