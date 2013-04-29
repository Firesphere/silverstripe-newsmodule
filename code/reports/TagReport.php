<?php
/**
 * Content side-report listing newsitems with broken comments
 * 
 * @package News/Blog module
 * @author Simon 'Sphere'
 * @todo Semantics
 */

class TagReport extends SS_Report {

	/**
	 * Set the title
	 * @return String Report Title
	 */
	public function title() {
		return _t($this->class . '.TITLE', 'Tag usage report');
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
		$where = null;
		if(isset($params['Title']) && $params['Title'] != ''){
			$where = 'Title LIKE \'%'.$params['Title'].'%\'';
		}
		$ret = Tag::get()->where($where);
		$returnSet = new ArrayList();
		if ($ret) foreach($ret as $record) {
			$record->Itemcount = $record->News()->count();
			$returnSet->push($record);
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
					'<a href=\"admin/news/Tag/EditForm/field/Tag/item/$ID/edit\" title=\"%s\">$value</a>',
					_t($this->class . '.EDIT', 'Edit tag')
				)
			),
			"Itemcount" => array(
				"title" => _t($this->class . '.NEWSCOUNT', 'Total items linked'),
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
		return FieldList::create(
			TextField::create(
				'Title', 
				_t($this->class . '.TAGTITLE', 'Search tag')
			)
		);
	}
}
