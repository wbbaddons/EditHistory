<?php
namespace wbb\data\user; 

use wcf\system\WCF; 
use wcf\system\database\util\PreparedStatementConditionBuilder; 
use wbb\data\post\history\version\PostHistoryVersionAction; 

/**
 * Actions for the edit-history
 * 
 * @author	Joshua Rüsweg
 * @package	de.joshsboard.edithistory
 */
class EditHistoryUserAction extends \wcf\data\user\UserAction {

	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'ban', 'delete', 'disable', 'enable', 'unban', 'countLastEdits', 'revertLastEdits');
	
	const PERIODE = 604800; // = 7 days
	
	/**
	 * validate the countLastEdits()-methode
	 */
	public function validateCountLastEdits() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		WCF::getSession()->checkPermissions(array('mod.board.canViewPostVersions'));
	}
	
	/**
	 * count the edited posts in the last 7 days for a specific user
	 * 
	 * @return array<int>
	 */
	public function countLastEdits() {
		$edits = array(); 
		
		foreach ($this->objectIDs as $userID) { 
			$sql = "SELECT COUNT(*) as count FROM wbb" . WCF_N . "_post_history_version post_history_version WHERE time > ? AND userID = ? AND versionID IN (SELECT MAX(versionID) FROM wbb" . WCF_N . "_post_history_version GROUP BY postID)";
			$stmt = WCF::getDB()->prepareStatement($sql);
			$stmt->execute(array(TIME_NOW - self::PERIODE, $userID));
			$edits[$userID] = $stmt->fetchColumn();
		}
		
		return $edits; 
		
	}
	
	/**
	 * validate the revertLastEdits()-methode
	 */
	public function validateRevertLastEdits() {
		$this->validateCountLastEdits();
	}
	
	/**
	 * revert the last edits for specific users
	 */
	public function revertLastEdits() {
		// that could be complicated ..
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($this->objectIDs));
		$conditions->add('time > ?', array(TIME_NOW - self::PERIODE));
		$conditions->add("versionID IN (SELECT MAX(versionID) FROM wbb" . WCF_N . "_post_history_version GROUP BY postID)", array()); 
		
		// first, we should select the postings
		$sql = "SELECT postID FROM wbb" . WCF_N . "_post_history_version post_history_version ".$conditions;
		$stmt = WCF::getDB()->prepareStatement($sql);
		$stmt->execute($conditions->getParameters());

		$rversion = array(); 
		
		while ($postID = $stmt->fetchArray()) {
			$postID = $postID['postID'];
			
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->enableWhereKeyword(false); 
			$conditions->add("post_history_version.userID NOT IN(?)", $this->objectIDs);
			
			$sql = "SELECT versionID FROM wbb" . WCF_N . "_post_history_version post_history_version WHERE (post_history_version.time > ? AND ". $conditions ." AND post_history_version.postID = ?) OR (post_history_version.time < ? AND post_history_version.postID = ?) ORDER BY post_history_version.time DESC LIMIT 1";
			$stmt = WCF::getDB()->prepareStatement($sql);
			$stmt->execute(array_merge(array(TIME_NOW - self::PERIODE), $conditions->getParameters(), array($postID, TIME_NOW - self::PERIODE, $postID)));
			$version = $stmt->fetchColumn();
			if ($version != 0) {
				$rversion[] = intval($version);
			} else {
				// try to fetch the first version
				$sql = "SELECT MIN(versionID) FROM wbb" . WCF_N . "_post_history_version WHERE postID = ? GROUP BY postID";
				$stmt = WCF::getDB()->prepareStatement($sql); 
				$stmt->execute(array($postID)); 
				$rversion[] = intval($stmt->fetchColumn()); 
			}
		}
		
		$action = new PostHistoryVersionAction($rversion, 'revert');
		
		try {
			$action->validateAction();
		} catch (\wcf\system\exception\PermissionDeniedException $e) {
			// we should ignore this
			// because this is an board-exception
		}
		
		$action->executeAction();
		
		$this->unmarkItems(); 
	}
}