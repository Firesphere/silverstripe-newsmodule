<?php
/**
 * Remove or comment the following lines to enable/disable features.
 * You can register the YouTube feature to other shortcodes, but I prefer shorter bb-code.
 * 
 * To enable, just add a / directly after the star below this line
 *
// It seems an array bugs out :(
// Can this be done via a yml file?
ShortcodeParser::get()->register('tweet',array('NewsHolderPage','TweetHandler'));
ShortcodeParser::get()->register('code',array('NewsHolderPage','GeshiParser'));
ShortcodeParser::get()->register('YT',array('NewsHolderPage','YouTubeHandler'));
ShortcodeParser::get()->register('slideshow', array('NewsHolderPage','createSlideshow'));
/*This comment is here to save you from WTF HAPPENED!*/