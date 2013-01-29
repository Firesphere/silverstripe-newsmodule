<?php

global $lang;

$curLang = 'nl_NL';

/* pagetype names */
$lang[$curLang]['NewsHolderPage']['PLURALNAME'] = 'Nieuwsoverzicht pagina\'s';
$lang[$curLang]['NewsHolderPage']['SINGULARNAME'] = 'Nieuwsoverzicht pagina';


// used by the main menu:
$lang[$curLang]['NewsAdmin']['MENUTITLE'] = 'Nieuws';

// the human readable names for the model
$lang[$curLang]['News']['SINGULARNAME'] = 'Nieuwsitem';
$lang[$curLang]['News']['PLURALNAME'] = 'Nieuwsitems';

// summary_fields
$lang[$curLang]['News']['TITLE'] = 'Titel';
$lang[$curLang]['News']['PUBLISH_FROM'] = 'Tonen vanaf';
$lang[$curLang]['News']['PUBLISH_UNTIL'] = 'Tonen t/m';
$lang[$curLang]['News']['FORCE_TO_FRONTPAGE'] = 'Vastgezet op de voorpagina';

// getCMSFields
$lang[$curLang]['News']['CONTENT'] = 'Content';
$lang[$curLang]['News']['FORCE_TO_FRONTPAGE_WI'] = 'Toon dit bericht op de voorpagina, ook al zijn er nieuwere items';
$lang[$curLang]['News']['PUBLISH_FROM_WI'] = 'Toon dit nieuwsbericht vanaf deze datum (indien u geen datum invult, dan wordt het direct getoond)';
$lang[$curLang]['News']['PUBLISH_UNTIL_WI'] = 'Toon dit nieuwsbericht tot en met deze datum (daarna zal deze ook niet meer getoond worden in het archief!)';

// permission provider
$lang[$curLang]['NewsAdmin_Controller']['NEWS_PERMISSIONS_CATEGORY'] = 'Nieuws permissions';
$lang[$curLang]['NewsAdmin_Controller']['CREATE_NEWS'] = 'Nieuwsitems aanmaken';
$lang[$curLang]['NewsAdmin_Controller']['CREATE_NEWS_HELP'] = 'Geef de gebruiker de mogelijk om nieuwsitems aan te maken';
$lang[$curLang]['NewsAdmin_Controller']['EDIT_NEWS'] = 'Nieuwsitems bewerken';
$lang[$curLang]['NewsAdmin_Controller']['EDIT_NEWS_HELP'] = 'Geef de gebruiker de mogelijk om nieuwsitems te bewerken';
$lang[$curLang]['NewsAdmin_Controller']['DELETE_NEWS'] = 'Nieuwsitems verwijderen';
$lang[$curLang]['NewsAdmin_Controller']['DELETE_NEWS_HELP'] = 'Geef de gebruiker de mogelijk om nieuwsitems te verwijderen';

/* newsholderpage */
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_LABEL'] = 'Let op!';
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_P'] = 'U bewerkt hier alleen de nieuwsoverzichtspagina!';
$lang[$curLang]['NewsHolderPage']['MANAGE_NOTE_BUTTON'] = 'Klik hier om nieuwsitems te beheren';

/* cms templates */
$lang[$curLang]['NewsTableListField_Item.ss']['FORCE_TO_FRONTPAGE'] = 'Vastgezet op de voorpagina';
$lang[$curLang]['NewsTableListField_Item.ss']['TOGGLE_FORCE_TO_FRONTPAGE'] = 'Instelling -Vastgezet op de voorpagina- wijzigen';
