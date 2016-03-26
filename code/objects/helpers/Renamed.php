<?php

/**
 * Handle renamed topics.
 * When a post is being renamed, it's URLSegment is updated.
 * To prevent bookmarks and Spiders to get completely lost, we store the old link and redirect according to the news which it belongs to.
 * This functionality is chosen in favor of fully keeping a history of all the news.
 *
 * This class is not available for Renaming uses, therefor it's unavailable in the backend.
 * It is enough to handle it, that should do the trick just fine.
 *
 * @package News/Blog module
 * @author Simon `Sphere`
 *
 * StartGeneratedWithDataObjectAnnotator
 * @property string OldLink
 * @property int NewsID
 * @method News News
 * EndGeneratedWithDataObjectAnnotator
 */
class Renamed extends DataObject
{
    private static $db = array(
        'OldLink' => 'Varchar(255)'
    );
    private static $has_one = array(
        'News' => 'News',
    );
    private static $indexes = array(
        'OldLink' => true
    );

}
