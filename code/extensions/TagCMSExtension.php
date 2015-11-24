<?php

/**
 * Handler for the CMS options for tags. This cleans up the Tags class.
 * This is pure for readability, it has no super powers.
 *
 * @author Simon 'Sphere' Erkelens
 *
 * StartGeneratedWithDataObjectAnnotator
 * @property Tag|TagCMSExtension owner
 * EndGeneratedWithDataObjectAnnotator
 */
class TagCMSExtension extends DataExtension
{

	/**
	 * @todo fix sortorder
	 * @param FieldList $fields
	 */
	public function updateCMSFields(FieldList $fields)
	{
		/** Setup new Root Fieldlist */
		$fields->removeByName('Main');
		$owner = $this->owner;
		/** Add the fields */
		$fields->addFieldsToTab(
			'Root', // To what tab
			Tab::create(
				'Main', // Name
				_t('Tag.MAIN', 'Main'), // Title
				/** Fields */
				$text = TextField::create('Title', $owner->fieldLabel('Title')), $html = HTMLEditorField::create('Description', $owner->fieldLabel('Description')), $uplo = UploadField::create('Impression', $owner->fieldLabel('Impression'))
			)
		);
	}

}
