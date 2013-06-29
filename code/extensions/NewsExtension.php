<?php
/**
 * Make the news globally available. So you don't have to be on a NewsHolderPage.
 * Same goes for tags. For if you want a tagcloud in your sidebar, for example.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 * @todo Better comments
 * @todo Semantics
 */
class NewsExtension extends DataExtension {

	/**
	 * Get all, or a limited, set of items.
	 * @param $limit integer with chosen limit. Called from template via <% loop NewsArchive(5) %> for the 5 latest items.
	 * @param $random boolean Called from template. e.g. <% loop NewsArchive(5,1) %> to show random posts, related via the tags.
	 * @param $related boolean Called from template. e.g. <% loop NewsArchive(5,0,1) %> to show just the latest 5 related items.
	 *	Or, to show 5 random related items, use <% loop NewsArchive(5,1,1) %>. You're free to play with the settings :)
	 *	To loop ALL items, set the first parameter (@param $limit) to zero. As you can see.
	 * @todo implement subsites
	 */
	public function NewsArchive($limit = 5, $random = null, $related = null) {
		if($limit == 0){
			$limit = null;
		}
		$Params = $this->owner->getURLParams();
		$filter = array(
			'Live' => 1,
			'PublishFrom:LessThan' => date('Y-m-d H:i:s', strtotime('Tomorrow'))
		);
		if(class_exists('Translatable')){
			$filter['Locale'] = Translatable::get_current_locale();
		}
		/**
		 * It's too bad chaining doesn't work :/ Therefor, we have a bunch of extended if's
		 */
		if($Params['Action'] == 'show' && $related) {
			$otherNews = News::get()
				->filter(
					array('URLSegment' => $Params['ID'])
				)
				->first();
			if($random){
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter($filter)
					->exclude(
						array('ID' => $otherNews->ID)
					)
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter($filter)
					->exclude(
						array('ID' => $otherNews->ID)
					)
					->limit($limit);
			}
		} else {
			if($random){
				$news = News::get()
					->filter($filter)
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter($filter)
					->limit($limit);
			}
		}
		if($news->count() == 0){
			return null;
		}
		return($news);
	}

	/**
	 * Get the NewsItems as groupedList for global archive-listing.
	 * @return GroupedList of NewsItems.
	 */
	public function getArchiveList(){
		$NewsGroups = GroupedList::create(News::get());
		return $NewsGroups;
	}

	/**
	 * Just get all tags.
	 * @todo support translatable?
	 * @return type Datalist of all tags
	 */
	public function allTags() {
		return Tag::get();
	}

    /**
     * Get all the items from a single newsholderPage.
     * @param $limit integer with chosen limit. Called from template via <% loop $NewsArchiveByHolderID(321,5) %> for the page with ID 321 and 5 latest items.
     * @todo many things, isn't finished
     * @fixed I refactored a bit. Only makes for a smaller function.
     * @author Marcio Barrientos
     */
    public function NewsArchiveByHolderID($holderID = null, $limit = 5 ){
	$filter = array(
	    'Live' => 1,
	    'NewsHolderPageID' => $holderID,
	    'PublishFrom:LessThan' => date('Y-m-d H:i:s', strtotime('Tomorrow'))
	);
	if($limit == 0){
            $limit = null;
        }
        if(class_exists('Translatable')){
	    $filter['Locale'] = Translatable::get_current_locale();
        }

        $news = News::get()
            ->filter($filter)
            ->limit($limit);

        if($news->count() == 0){
            return null;
        }

        return $news;
    }
	
}
