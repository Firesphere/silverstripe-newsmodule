<?php

/**
 * Slideshow Image is the holder for the images for the slideshow.
 *
 * @package News/Blog module
 * @author Simon `Sphere`
 * @property string Title
 * @property HTMLText Description
 * @property Int SortOrder
 * @method Image Image() Image for this group
 * @method News News() this image belongs to
 */
class SlideshowImage extends DataObject
{
	/** @var array $db */
	private static $db = array(
		'Title'       => 'Varchar(255)',
		'Description' => 'HTMLText',
		'SortOrder'   => 'Int',
	);

	/** @var array $has_one */
	private static $has_one = array(
		'Image' => 'Image',
		'News'  => 'News',
	);

	/**
	 * Setup the Fields labels with the correct translation (if needed)
	 * @param boolean $includerelations
	 * @return array The final translations.
	 */
	public function fieldLabels($includerelations = true)
	{
		$labels = parent::fieldLabels($includerelations);
		$slideshowImageLabels = array(
			'Title'       => _t('SlideshowImage.TITLE', 'Title'),
			'Description' => _t('SlideshowImage.DESCRIPTION', 'Description'),
			'Image'       => _t('SlideshowImage.IMAGE', 'Image'),
		);

		return array_merge($slideshowImageLabels, $labels);
	}

	public function onAfterWrite()
	{
		parent::onAfterWrite();
		/** @var SiteConfig $siteConfig Limit uploaded images to the setting in the siteconfig. */
		$siteConfig = SiteConfig::current_site_config();
		if ($siteConfig->SlideshowSize) {
			$this->resizeImages($siteConfig);
		}
	}

	/**
	 * If there's a max-size set in the SiteConfig ({width}x{height}) for the image, resize the image.
	 * This saves space on the hosting and prevents huge high-res images to stay online for no reason.
	 * @param SiteConfig $siteConfig
	 */
	public function resizeImages(SiteConfig $siteConfig)
	{
		$splitter = trim(str_replace(range(0, 9), '', $siteConfig->SlideshowSize));
		$size = explode($splitter, $siteConfig->SlideshowSize);
		if ($this->Image()->getWidth() > $size[0]) {
			$maxSized = $this->Image()->SetWidth($size[0]);

			unlink(Director::baseFolder() . '/' . $this->Image()->getFilename());
			copy(Director::baseFolder() . '/' . $maxSized->getFilename(), Director::baseFolder() . '/' . $this->Image()->getFilename());
			unlink(Director::baseFolder() . '/' . $maxSized->getFilename());
		}
		if ($this->Image()->getHeight() > $size[1]) {
			$maxSized = $this->Image()->SetHeight($size[1]);

			unlink(Director::baseFolder() . '/' . $this->Image()->getFilename());
			copy(Director::baseFolder() . '/' . $maxSized->getFilename(), Director::baseFolder() . '/' . $this->Image()->getFilename());
			unlink(Director::baseFolder() . '/' . $maxSized->getFilename());
		}
	}

	/**
	 * Permissions
	 */
	public function canCreate($member = null)
	{
		return (Permission::checkMember($member, array('CREATE_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canEdit($member = null)
	{
		return (Permission::checkMember($member, array('EDIT_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canDelete($member = null)
	{
		return (Permission::checkMember($member, array('DELETE_NEWS', 'CMS_ACCESS_NewsAdmin')));
	}

	public function canView($member = null)
	{
		return (Permission::checkMember($member, array('VIEW_NEWS', 'CMS_ACCESS_NewsAdmin')) || $this->Live == 1);
	}

}
