<?php
/**
 * Content side-report listing newsitems with broken comments
 * 
 * @package News/Blog module
 * @author Simon 'Sphere'
 */

class CommentReport extends SS_Report {

	/**
	 * Set the title
	 * @return String Report Title
	 */
	public function title() {
		return _t($this->class . '.TITLE', 'Newscomment report');
	}
	
	/**
	 * Setup the list of records to show.
	 * @param type $params array of filter-rules.
	 * @param type $sort 
	 * @param type $limit
	 * @return \ArrayList with the records.
	 */
	public function sourceRecords($params, $sort, $limit) {
		if($sort) {
			$parts = explode(' ', $sort);
			$field = $parts[0];
			$direction = $parts[1];
		}
		$filter = array(
			'Comments.ID:GreaterThan' => 0,
		);
		$where = null;
		if(count($params) > 0 && isset($params['Title'])){
			$where = 'News.Title LIKE \'%'.$params['Title'].'%\'';
		}
		$ret = News::get()->filter($filter)->sort('IF(PublishFrom, PublishFrom, News.Created) DESC')->where($where);
		$returnSet = new ArrayList();
		if ($ret) foreach($ret as $record) {
			$record->Commentcount = $record->Comments()->count();
			$record->Spamcount = $record->Comments()->filter(array('AkismetMarked' => 1))->count();
			$record->Hiddencount = $record->Comments()->filter(array('AkismetMarked' => 0, 'Visible' => 0))->count();
			
			if(isset($params['Comment']) && $params['Comment'] == 'SPAMCOUNT' && $record->Spamcount > 0){
				$returnSet->push($record);
			}
			elseif(isset($params['Comment']) && $params['Comment'] == 'HIDDENCOUNT' && $record->Hiddencount > 0){
				$returnSet->push($record);
			}
			elseif((isset($params['Comment']) && $params['Comment'] == '') || !isset($params['Comment'])){
				$returnSet->push($record);
			}
		}		
		return $returnSet;
	}
	
	/**
	 * Setup the columns in the list.
	 * @todo it seems HiddenCount bugs out. No idea why.
	 * @return array of fields.
	 */
	public function columns() {
		$fields = array(
			"Title" => array(
				"title" => _t($this->class . '.NEWSTITLE', 'Item title'),
				'formatting' => sprintf(
					'<a href=\"admin/news/News/EditForm/field/News/item/$ID/edit\" title=\"%s\">$value</a>',
					_t($this->class . '.EDIT', 'Edit item')
				)
			),
			"Commentcount" => array(
				"title" => _t($this->class . '.COMMENTCOUNT', 'Total comments'),
				'casting' => 'Int'
			),
			"Spamcount" => array(
				"title" => _t($this->class . '.COMMENTCOUNT', 'Spam comments'),
				'casting' => 'Int'
			),
			"Hiddencount" => array(
				"title" => _t($this->class . '.HIDDENCOUNT', 'Hidden comments'),
				'casting' => 'Int'
			),
		);
		
		return $fields;
	}
	
	/**
	 * Setup the searchform.
	 * @return \FieldList FieldList instance with the searchfields.
	 */
	public function parameterFields() {
		$return = FieldList::create(
			$title = TextField::create(
				'Title', 
				_t($this->class . '.NEWSSEARCHTITLE', 'Search newsitem')
			),
			$count = DropdownField::create(
				'Comment', 
				_t($this->class . '.COUNTFILTER', 'Comment count'), 
				array(
					'' => _t($this->class . '.ANY', 'Any'),
					'SPAMCOUNT' => _t($this->class . '.SPAMCOUNT', 'One or more spam comments'),
					'HIDDENCOUNT' => _t($this->class . '.HIDDENCOUNT', 'One or more hidden comments'),
				)
			)
		);
		return $return;
	}
}
