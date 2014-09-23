<?php
/**
 * Content side-report listing tags and amount of usage.
 * 
 * @package News/Blog module
 * @author Simon 'Sphere'
 * @todo Semantics
 */
class TagReport extends SS_Report
{

	/**
	 * Set the title. Because I still don't know why not.
	 * @return String Report Title
	 */
	public function title()
	{
		return _t('TagReport.TITLE', 'Tag usage report');
	}

	/**
	 * Setup the list of records to show.
	 * @param type $params array of filter-rules.
	 * @param type $sort 
	 * @param type $limit
	 * @return \ArrayList with the records.
	 */
	public function sourceRecords($params, $sort, $limit)
	{
		if ($sort) {
			$parts = explode(' ', $sort);
			$field = $parts[0];
			$direction = $parts[1];
		}
		$where = null;
		if (isset($params['Title']) && $params['Title'] != '') {
			$where = 'Title LIKE \'%' . $params['Title'] . '%\'';
		}
		$ret = Tag::get()->where($where);
		$returnSet = new ArrayList();
		if ($ret)
			foreach ($ret as $record) {
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
	public function columns()
	{
		$fields = array(
			"Title" => array(
				"title" => _t('TagReport.NEWSTITLE', 'News title'),
				'formatting' => sprintf(
					'<a href=\"admin/news/Tag/EditForm/field/Tag/item/$ID/edit\" title=\"%s\">$value</a>', _t('TagReport.EDIT', 'Edit tag')
				)
			),
			"Itemcount" => array(
				"title" => _t('TagReport.NEWSCOUNT', 'Total items linked'),
				'casting' => 'Int'
			),
		);

		return $fields;
	}

	/**
	 * Setup the searchform.
	 * @return \FieldList FieldList instance with the searchfields.
	 */
	public function parameterFields()
	{
		$return = FieldList::create(
				$title = TextField::create(
					'Title', _t('TagReport.TAGTITLE', 'Search for tag')
				)
		);
		return $return;
	}

}