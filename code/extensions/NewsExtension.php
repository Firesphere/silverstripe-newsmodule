<?php
/**
 * Make the news globally available. So you don't have to be on a NewsHolderPage.
 * Same goes for tags. For if you want a tagcloud in your sidebar, for example.
 * 
 * @package News/blog module
 * @author Simon 'Sphere' 
 */
class NewsExtension extends DataExtension {

	/**
	 * Get all, or a limited, set of items.
	 * @param $limit integer with chosen limit. Called from template via <% loop NewsArchive(5) %> for the 5 latest items.
	 * @param $random boolean Called from template. e.g. <% loop NewsArchive(5,1) %> to show randomly related posts via the tags.
	 * @param $related boolean Called from template. e.g. <% loop NewsArchive(5,0,1) %> to show just the latest 5 items.
	 *	Or, to show 5 random items, use <% loop NewsArchive(5,1,1) %>. You're free to play with the settings :)
	 *	To loop ALL items, set the first parameter (@param $limit) to zero. As you can see.
	 * @todo fix an admin-like feature. If the user has the correct permissions, show all posts, not only live ones.
	 */
	public function NewsArchive($limit = null, $random = null, $related = null) {
		if($limit == 0){
			$limit = null;
		}
		$Params = $this->owner->getURLParams();
		if($Params['Action'] == 'show' && $related) {
			$otherNews = News::get()
				->filter(array('URLSegment' => $Params['ID']))
				->first();
			if($random){
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->exclude(array('ID' => $otherNews->ID))
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->exclude(array('ID' => $otherNews->ID))
					->limit($limit);
			}
		} else {
			if($random){
				$news = News::get()
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter(array('Live' => 1))
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->limit($limit);				
			}
		}
		if($news->count() == 0){
			return null;
		}
		return($news);
	}


	/**
	 * Just get al tags.
	 * @return type Datalist of all tags
	 */
	public function allTags() {
		return Tag::get();
	}
	
}
