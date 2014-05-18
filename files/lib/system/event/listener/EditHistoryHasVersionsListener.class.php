<?php
namespace wbb\system\event\listener;

use wcf\system\event\IEventListener; 
use wcf\system\WCF; 
use wcf\system\database\util\PreparedStatementConditionBuilder; 

/**
 * check, wheather the posts has versions
 * 
 * @author         Joshua Rüsweg
 * @package        de.joshsboard.edithistory
 */
class EditHistoryHasVersionsListener implements IEventListener {

	/**
	 * @see        IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// fetch all postIDs
		$postIDs = array(); 
			
		foreach ($eventObj->objectList as $post) {
			$postIDs[] = $post->postID; 
		}
                
		// select all versions
                $conditions = new PreparedStatementConditionBuilder(); 
                $conditions->add("post_history_version.postID IN (?)", array($postIDs));
                
                $sql = "SELECT post_history_version.postID FROM wbb".WCF_N."_post_history_version post_history_version ".$conditions." GROUP BY post_history_version.postID";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute($conditions->getParameters());
                
		$hasVersions = array(); 
		
		while ($row = $statement->fetchArray()) {
			$hasVersions[$row['postID']] = true;
		}
            
		WCF::getTPL()->assign(array(
		    'hasVersions' => $hasVersions
		));
	}

}