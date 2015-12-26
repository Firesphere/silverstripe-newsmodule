<?php

/**
 * Make the news globally available. So you don't have to be on a NewsHolderPage.
 * Same goes for tags. For if you want a tagcloud in your sidebar, for example.
 *
 * @package News/blog module
 * @author  Simon 'Sphere'
 * @todo    Better comments
 * @todo    Semantics
 * @property Page_Controller|NewsControllerExtension owner
 */
class NewsControllerExtension extends DataExtension
{

    /**
     * Get all, or a limited, set of items.
     *
     * @param $limit   integer with chosen limit. Called from template via <% loop NewsArchive(5) %> for the 5 latest items.
     * @param $random  boolean Called from template. e.g. <% loop NewsArchive(5,1) %> to show random posts, related via the tags.
     * @param $related boolean Called from template. e.g. <% loop NewsArchive(5,0,1) %> to show just the latest 5 related items.
     *                 Or, to show 5 random related items, use <% loop NewsArchive(5,1,1) %>. You're free to play with the settings :)
     *                 To loop ALL items, set the first parameter (@param $limit ) to zero. As you can see.
     *
     * @return DataList|News[]
     * @todo implement subsites
     */
    public function NewsArchive($limit = 5, $random = null, $related = null)
    {
        if ($limit === 0) {
            $limit = null;
        }
        $params = $this->owner->getURLParams();
        $otherNews = null;
        /** @var News $otherNews */
        if ($related) {
            $otherNews = $this->owner->getNews();
        }
        if ($otherNews || !$related) {
            return $this->getArchiveItems($otherNews, $limit, $random, $related, $params);
        }
    }

    /**
     * Get the NewsItems as groupedList for global archive-listing.
     * @todo obey translatable maybe? I think it's supported by default, but I could be wrong
     * @return GroupedList of NewsItems.
     */
    public function getArchiveList()
    {
        return GroupedList::create(News::get());
    }

    /**
     * Just get all tags.
     * @todo support translatable?
     * @return ArrayList|Tag[] of all tags
     */
    public function allTags()
    {
        return Tag::get();
    }

    /**
     * Get all the items from a single newsholderPage.
     *
     * @param int $holderID
     * @param     $limit integer with chosen limit. Called from template via <% loop $NewsArchiveByHolderID(321,5) %> for the page with ID 321 and 5 latest items.
     *
     * @todo   many things, isn't finished
     * @fixed  I refactored a bit. Only makes for a smaller function.
     * @author Marcio Barrientos
     * @return ArrayList|News[]
     */
    public function NewsArchiveByHolderID($holderID = null, $limit = 5)
    {
        $filter = array(
            'Live'                 => 1,
            'NewsHolderPageID'     => $holderID,
            'PublishFrom:LessThan' => SS_Datetime::now()->Rfc2822(),

        );
        if ($limit === 0) {
            $limit = null;
        }
        if (class_exists('Translatable')) {
            $filter['Locale'] = Translatable::get_current_locale();
        }
        $news = News::get()
            ->filter($filter)
            ->limit($limit);

        if ($news->count() === 0) {
            return null;
        }

        return $news;
    }

    /**
     * @param News        $otherNews
     * @param int         $limit
     * @param bool|string $sort
     * @param bool        $related
     * @param array       $params
     *
     * @return DataList|null|SS_Limitable
     */
    private function getArchiveItems($otherNews, $limit, $sort, $related, $params)
    {
        $filter = array(
            'Live'                 => 1, // only work on live items
            'PublishFrom:LessThan' => SS_Datetime::now()->Rfc2822(), // same as above
        );
        if (class_exists('Translatable')) {
            $filter['Locale'] = Translatable::get_current_locale();
        }
        $exclude = array();

        if ($related && $params['Action'] === 'show') { // @todo This only works with the default action, not allowed actions
            $filter = array_merge(
                $filter,
                array('Tags.ID:ExactMatch' => $otherNews->Tags()->column('ID'))
            );
            $exclude = array_merge(
                $exclude, array('ID' => $otherNews->ID)
            );
        }
        if ($sort) {
            $sort = 'RAND()';
        }
        /** @var DataList||News[] $news */
        $news = News::get()
            ->filter($filter)
            ->exclude($exclude)
            ->sort($sort)
            ->limit($limit);

        return $news;
    }

}
