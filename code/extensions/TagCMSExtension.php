<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of TagCMSExtension
 *
 * @author Simon 'Sphere' Erkelens
 */
class TagCMSExtension extends DataExtension {
	
	/**
	 * @todo fix sortorder
	 * @return FieldList $fields Fields that are editable.
	 */
	public function updateCMSFields(FieldList $fields) {
		/** Setup new Root Fieldlist */
		$fields->removeByName('Main');
		/** Add the fields */
		$fields->addFieldsToTab(
			'Root', // To what tab
			Tab::create(
				'Main', // Name
				_t('Tag.MAIN', 'Main'), // Title
				/** Fields */
				$text = TextField::create('Title', _t('Tag.TITLE', 'Title')),
				$html = HTMLEditorField::create('Description', _t('Tag.DESCRIPTION', 'Description')),
				$uplo = UploadField::create('Impression', _t('Tag.IMPRESSION', 'Impression image'))
			)
		);
	}
}