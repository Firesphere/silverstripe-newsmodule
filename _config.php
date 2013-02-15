<?php

// make News-functions available to controller and templates:
Object::add_extension('ContentController', 'NewsExtension');

//Searchables in News
Object::add_extension('SiteConfig', 'NewsSiteConfigDecorator');

SSAkismet::setAPIKey('YOURAPIKEY');

/**
 * Remove or comment the following lines to disable features.
 */
ShortcodeParser::get()->register('tweet',array('NewsHolderPage','TweetHandler'));
ShortcodeParser::get()->register('code',array('NewsHolderPage','GeshiParser'));
ShortcodeParser::get()->register('YT',array('NewsHolderPage','YouTubeHandler'));
