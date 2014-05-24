<?php
namespace wbb\system\event\listener;

use wcf\system\event\IEventListener; 
use wbb\data\post\history\version\PostHistoryVersionList; 
use wbb\data\post\history\version\PostHistoryVersionAction;

/**
 * clean up outdated versions in the DailyCronjob
 * 
 * @author         Joshua Rüsweg
 * @package        de.joshsboard.edithistory
 */
class EditHistoryCronjobListener implements IEventListener {

	/**
	 * @see        IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// clean up outdated versions
		if (WBB_POSTEDITHISTORY_DELETEVERSIONSAFTER != 0) {
			// delete outdated versions
			$versionList = new PostHistoryVersionList();
			$versionList->getConditionBuilder()->add('post_history_version.created < ?', array(TIME_NOW - 86400 * WBB_POSTEDITHISTORY_DELETEVERSIONSAFTER));
			$versionList->readObjects();

			if (!$versionList->count()) return;

			$versionAction = new PostHistoryVersionAction($versionList->getObjects(), 'delete');
			$versionAction->executeAction();
		}
	}

}