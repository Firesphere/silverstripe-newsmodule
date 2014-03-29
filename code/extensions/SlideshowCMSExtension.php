<?php
/**
 * This extension sets up the fields for the Slideshow images.
 *
 * @author Simon 'Sphere' Erkelens
 */
class SlideshowCMSExtension extends DataExtension {
	/**
	 * Setup the CMSFields
	 * @return FieldList $fields Fields to be shown in the admin.
	 */
	public function updateCMSFields(FieldList $fields) {
		$owner = $this->owner;
		$fields->removeByName(array('News', 'NewsID','SortOrder'));
		$fields->addFieldsToTab(
			'Root.Main',
			array(
				TextField::create('Title', $owner->fieldLabel('Title')),
				HtmlEditorField::create('Description', $owner->fieldLabel('Description')),
				UploadField::create('Image', $owner->fieldLabel('Image')),
			)
		);
	}
}