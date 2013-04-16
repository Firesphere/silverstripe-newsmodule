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
	 * @param $random boolean Called from template. e.g. <% loop NewsArchive(5,1) %> to show random posts, related via the tags.
	 * @param $related boolean Called from template. e.g. <% loop NewsArchive(5,0,1) %> to show just the latest 5 related items.
	 *	Or, to show 5 random related items, use <% loop NewsArchive(5,1,1) %>. You're free to play with the settings :)
	 *	To loop ALL items, set the first parameter (@param $limit) to zero. As you can see.
	 */
	public function NewsArchive($limit = 5, $random = null, $related = null) {
		if($limit == 0){
			$limit = null;
		}
		$Params = $this->owner->getURLParams();
		if(class_exists('Translatable')){
			$filter = array(
				'Live' => 1, 
				'Locale' => Translatable::current_lang()
			);
		}
		else{
			$filter = array(
				'Live' => 1,
			);
		}
		if($Params['Action'] == 'show' && $related) {
			$otherNews = News::get()
				->filter(array('URLSegment' => $Params['ID']))
				->first();
			if($random){
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter($filter)
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->exclude(array('ID' => $otherNews->ID))
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter('Tags.ID:ExactMatch', $otherNews->Tags()->column('ID'))
					->filter($filter)
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->exclude(array('ID' => $otherNews->ID))
					->limit($limit);
			}
		} else {
			if($random){
				$news = News::get()
					->filter($filter)
					->where('PublishFrom IS NULL OR PublishFrom <= ' . date('Y-m-d'))
					->sort('RAND()')
					->limit($limit);
			}
			else{
				$news = News::get()
					->filter($filter)
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
	 * @todo support translatable?
	 * @return type Datalist of all tags
	 */
	public function allTags() {
		return Tag::get();
	}
	
}
