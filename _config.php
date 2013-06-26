<?php
/**
 * Remove or comment the following lines to enable/disable features.
 * You can register the YouTube feature to other shortcodes, but I prefer shorter bb-code.
 * 
 * To enable, just add a / directly after the star below this line. To disable, remove the /
 */
ShortcodeParser::get()->register('tweet',array('ExtraShortcodeParser','TweetHandler'));
ShortcodeParser::get()->register('code',array('ExtraShortcodeParser','GeshiParser'));
ShortcodeParser::get()->register('YT',array('ExtraShortcodeParser','YouTubeHandler'));
ShortcodeParser::get()->register('slideshow', array('ExtraShortcodeParser','createSlideshow'));
/*This comment is here to save you from WTF HAPPENED!*/