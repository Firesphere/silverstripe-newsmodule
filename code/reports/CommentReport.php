<?php

/**
 * Content side-report listing newsitems with broken comments
 *
 * @package News/Blog module
 * @author Simon 'Sphere'
 */
class CommentReport extends SS_Report
{

	/**
	 * Set the title. Because titles are useful.
	 * @return string Report Title
	 */
	public function title()
	{
		return _t('CommentReport.TITLE', 'Comment report');
	}

	/**
	 * Setup the list of records to show.
	 * @param array $params array of filter-rules.
	 * @param array $sort
	 * @param integer $limit
	 * @return ArrayList with the records.
	 */
	public function sourceRecords($params, $sort, $limit)
	{
		if ($sort) {
			$parts = explode(' ', $sort);
			$field = $parts[0];
			$direction = $parts[1];
		}
		$filter = array(
			'Comments.ID:GreaterThan' => 0,
		);
		if (count($params) > 0 && isset($params['Title'])) {
			$filter['News.Title:PartialMatch'] = $params['Title'];
		}
		$ret = News::get()->filter($filter);
		$returnSet = new ArrayList();
		if ($ret) {
			foreach ($ret as $record) {
				$record->Commentcount = $record->Comments()->count();
				$record->Spamcount = $record->Comments()->filter(array('AkismetMarked' => 1))->count();
				$record->Hiddencount = $record->Comments()->filter(array('AkismetMarked' => 0, 'Visible' => 0))->count();

				if (isset($params['Comment']) && $params['Comment'] == 'SPAMCOUNT' && $record->Spamcount > 0) {
					$returnSet->push($record);
				} elseif (isset($params['Comment']) && $params['Comment'] == 'HIDDENCOUNT' && $record->Hiddencount > 0) {
					$returnSet->push($record);
				} elseif ((isset($params['Comment']) && $params['Comment'] == '') || !isset($params['Comment'])) {
					$returnSet->push($record);
				}
			}
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
			"Title"        => array(
				"title"      => _t('CommentReport.NEWSTITLE', 'News title'),
				'formatting' => sprintf(
					'<a href=\"admin/news/News/EditForm/field/News/item/$ID/edit\" title=\"%s\">$value</a>', _t('CommentReport.EDIT', 'Edit item')
				)
			),
			"Commentcount" => array(
				"title"   => _t('CommentReport.COMMENTCOUNT', 'Total amount of comments'),
				'casting' => 'Int'
			),
			"Spamcount"    => array(
				"title"   => _t('CommentReport.COMMENTSPAMCOUNT', 'Spam comments'),
				'casting' => 'Int'
			),
			"Hiddencount"  => array(
				"title"   => _t('CommentReport.HIDDENCOUNT', 'Hidden comments'),
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
				'Title', _t('CommentReport.NEWSSEARCHTITLE', 'Search newsitem')
			), $count = DropdownField::create(
			'Comment', _t('CommentReport.COUNTFILTER', 'Comment count filter'), array(
				''            => _t('CommentReport.ANY', 'All'),
				'SPAMCOUNT'   => _t('CommentReport.SPAMCOUNT', 'One or more spam comments'),
				'HIDDENCOUNT' => _t('CommentReport.HIDDENCOUNT', 'One or more hidden comments'),
			)
		)
		);

		return $return;
	}

}
