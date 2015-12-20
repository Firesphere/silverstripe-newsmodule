<?php

/**
 * To keep everything consistent an clean/readable, this class handles the
 * parts needed for the CMS to manage comments.
 *
 * @package Silverstripe
 * @subpackage Newsmodule
 * @author Simon 'Sphere' Erkelens
 *
 * StartGeneratedWithDataObjectAnnotator
 * @property Comment|CommentCMSExtension owner
 * EndGeneratedWithDataObjectAnnotator
 */
class CommentCMSExtension extends DataExtension
{

    /**
     * Setup the fields for the CMS.
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->removeByName(array('MD5Email', 'NewsID'));
        $fields->addFieldsToTab(
            'Root.Main', array(
                TextField::create('Title', $owner->fieldLabel('Title')),
                TextField::create('Name', $owner->fieldLabel('Name')),
                TextField::create('Email', $owner->fieldLabel('Email')),
                TextField::create('URL', $owner->fieldLabel('URL')),
                HtmlEditorField::create('Comment', $owner->fieldLabel('Comment')),
                CheckboxField::create('AkismetMarked', $owner->fieldLabel('AkismetMarked')),
                CheckboxField::create('Visible', $owner->fieldLabel('Visible')),
                CheckboxField::create('ShowGravatar', $owner->fieldLabel('ShowGravatar')),
            )
        );
    }
}
