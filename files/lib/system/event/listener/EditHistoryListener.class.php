<?php
namespace wbb\system\event\listener;

use wcf\system\event\IEventListener; 
use wcf\system\database\util\PreparedStatementConditionBuilder; 
use wbb\data\post\history\version\PostHistoryVersionAction; 
use wcf\system\WCF; 

/**
 * save the versions
 * 
 * @author        Joshua Rüsweg
 * @package        de.joshsboard.edithistory
 */
class EditHistoryListener implements IEventListener {

	/**
	 * @see        IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->getActionName() != 'update') return; 
		
		switch ($eventName) {
			case 'initializeAction':
				// check for aviable versions 
				$conditions = new PreparedStatementConditionBuilder(); 
				$conditions->add("post_history_version.postID IN (?)", array($eventObj->getObjectIDs()));

				$sql = "SELECT post_history_version.postID FROM wbb".WCF_N."_post_history_version post_history_version ".$conditions." GROUP BY post_history_version.postID";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());

				$objects = array(); 

				foreach ($eventObj->getObjects() as $post) {
					$objects[$post->postID] = $post; 
				}

				//$objects = array_flip($eventObj->getObjec()); 

				while ($row = $statement->fetchArray()) {
					if (isset($objects[$row['postID']])) unset($objects[$row['postID']]);
				}

				foreach ($objects as $post) {
					// add version
					$action = new PostHistoryVersionAction(array(), 'create', array(
					    'data' => array(
						'postID' => $post->postID, 
						'userID' => $post->userID,
						'username' => $post->username, 
						'message' => $post->getMessage(),
						'time' => $post->getTime(),
						'ipAddress' => $post->ipAddress
					    )
					));
					$action->executeAction(); 
				}
				break; 
			
			case 'finalizeAction':
				$parameters = $eventObj->getParameters();
				if (!isset($parameters['data']['message'])) return; 
				
				foreach ($eventObj->getObjects() as $post) {
					$action = new PostHistoryVersionAction(array(), 'create', array(
						'data' => array(
						    'postID' => $post->postID,
						    'message' => $parameters['data']['message']
						)
					    ));
					$action->executeAction();
				}
				break; 
		}
	}

}