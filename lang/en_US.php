<?php

global $lang;

$curLang = 'en_US';

// used by the main menu:
$lang[$curLang]['NewsAdmin']['MENUTITLE'] = 'News';

// the human readable names for the model
$lang[$curLang]['News']['SINGULARNAME'] = 'Newsitem';
$lang[$curLang]['News']['PLURALNAME'] = 'Newsitems';

// summary_fields and searchable_fields
$lang[$curLang]['News']['TITLE'] = 'Title';
$lang[$curLang]['News']['PUBLISH_FROM'] = 'Show from';
$lang[$curLang]['News']['PUBLISH_UNTIL'] = 'Show until';
$lang[$curLang]['News']['FORCE_TO_FRONTPAGE'] = 'Force to frontpage';

// getCMSFields
$lang[$curLang]['News']['CONTENT'] = 'Content';
$lang[$curLang]['News']['FORCE_TO_FRONTPAGE_WI'] = 'Show this newsitem on the frontpage, even if there are newer items';
$lang[$curLang]['News']['PUBLISH_FROM_WI'] = 'Show from (Newsitem will not show up before this date)';
$lang[$curLang]['News']['PUBLISH_UNTIL_WI'] = 'Show until (Newsitem will not be shown after this date, not even on the archivepage)';

// permission provider
$lang[$curLang]['NewsAdmin_Controller']['NEWS_PERMISSIONS_CATEGORY'] = 'News permissions';
$lang[$curLang]['NewsAdmin_Controller']['CREATE_NEWS'] = 'Create newsitems';
$lang[$curLang]['NewsAdmin_Controller']['CREATE_NEWS_HELP'] = 'Grant the user the ability to create newsitems';
$lang[$curLang]['NewsAdmin_Controller']['EDIT_NEWS'] = 'Edit newsitems';
$lang[$curLang]['NewsAdmin_Controller']['EDIT_NEWS_HELP'] = 'Grant the user the ability to edit newsitems';
$lang[$curLang]['NewsAdmin_Controller']['DELETE_NEWS'] = 'Delete newsitems';
$lang[$curLang]['NewsAdmin_Controller']['DELETE_NEWS_HELP'] = 'Grant the user the ability to delete newsitems';

/* newsholderpage */
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_LABEL'] = 'Attention!';
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_P'] = 'You are editing the newslistpage here!';
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_BUTTON'] = 'Click here to manage newsitems';

/* cms templates */
$lang[$curLang]['NewsTableListField_Item.ss']['FORCE_TO_FRONTPAGE'] = 'Force to frontpage';
$lang[$curLang]['NewsTableListField_Item.ss']['TOGGLE_FORCE_TO_FRONTPAGE'] = 'Switch -Force to frontpage-switch';
