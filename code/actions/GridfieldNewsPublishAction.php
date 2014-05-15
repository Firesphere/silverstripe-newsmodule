<?php

/**
 * Gridfield Action to publish / unpublish a news item
 * @author Werner Krauß <werner.krauss@netwerkstatt.at>
 */
class GridfieldNewsPublishAction implements GridField_ColumnProvider, GridField_ActionProvider
{
	public function augmentColumns($gridField, &$columns) {
		if(!in_array('Actions', $columns)) {
			$columns[] = 'Actions';
		}
	}

	public function getColumnAttributes($gridField, $record, $columnName) {
		return array('class' => 'col-buttons');
	}


	public function getColumnMetadata($gridField, $columnName) {
		if($columnName == 'Actions') {
			return array('title' => '');
		}
	}

	public function getColumnsHandled($gridField) {
		return array('Actions');
	}

	public function getColumnContent($gridField, $record, $columnName) {
		if(!$record->canEdit()) return;

		if ($record->isPublished()) {
			$field = GridField_FormAction::create($gridField, 'UnPublish'.$record->ID, false, "unpublish",
				array('RecordID' => $record->ID))
				->addExtraClass('gridfield-button-unpublish')
				->setAttribute('title', _t('SiteTree.BUTTONUNPUBLISH', 'Unpublish'))
				->setAttribute('data-icon', 'unpublish')
				->setDescription(_t('News.BUTTONUNPUBLISHDESC', 'Unpublish news item'));
		} else {
			$field = GridField_FormAction::create($gridField, 'Publish'.$record->ID, false, "publish",
				array('RecordID' => $record->ID))
				->addExtraClass('gridfield-button-publish')
				->setAttribute('title', _t('SiteTree.BUTTONSAVEPUBLISH', 'Save & Publish'))
				->setAttribute('data-icon', 'accept')
				->setDescription(_t('News.BUTTONUNPUBLISHDESC',
						'Publish news item'));
		}
		return  $field->Field();
	}

	public function getActions($gridField) {
		return array('publish','unpublish');
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		if ($actionName == 'publish' || $actionName = 'unpublish') {
			$item = $gridField->getList()->byID($arguments['RecordID']);
			if(!$item) {
				return;
			}
			if (!$item->canEdit()) {
				throw new ValidationException(_t('News.PublishPermissionFailure',
						'No permission to publish or unpublish news item'));
			}
			if($actionName == 'publish') {
				$item->doPublish();
			}
			if($actionName == 'unpublish') {
				$item->doUnpublish();
			}
		}

	}
} 