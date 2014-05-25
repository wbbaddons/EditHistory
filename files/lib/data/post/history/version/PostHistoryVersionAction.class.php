<?php
namespace wbb\data\post\history\version; 

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF; 
use wbb\data\post\Post;
use wbb\data\thread\Thread; 
use wcf\data\user\User; 
use wcf\system\database\util\PreparedStatementConditionBuilder; 
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\util\DiffUtil; 
use wcf\util\UserUtil; 

class PostHistoryVersionAction extends AbstractDatabaseObjectAction {

	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wbb\data\post\history\version\PostHistoryVersionEditor';

	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('mod.board.canViewPostVersions');

	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('mod.board.canViewPostVersions');

	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('countLastEdits', 'revertLastEdits'); 
	
	/**
	 * create a version for a post
	 */
	public function create() {
		if (LOG_IP_ADDRESS) {
			// add ip address
			if (!isset($this->parameters['data']['ipAddress'])) {
				$this->parameters['data']['ipAddress'] = WCF::getSession()->ipAddress;
			}
		}
		else {
			// do not track ip address
			if (isset($this->parameters['data']['ipAddress'])) {
				unset($this->parameters['data']['ipAddress']);
			}
		}
		
		// add time
		if (!isset($this->parameters['data']['time'])) {
			$this->parameters['data']['time'] = TIME_NOW;
		}
		
		if (!isset($this->parameters['data']['created'])) {
			$this->parameters['data']['created'] = TIME_NOW;
		}
		
		// add userID
		if (!isset($this->parameters['data']['userID'])) {
			$this->parameters['data']['userID'] = WCF::getUser()->userID;
		}
		
		// add username
		if (!isset($this->parameters['data']['username'])) {
			$this->parameters['data']['username'] = WCF::getUser()->username;
		}
		
		parent::create();
	}
	
	public function validateRevert() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		WCF::getSession()->checkPermissions(array('mod.board.canViewPostVersions'));
		
		// check wheather someone can revert a post
		foreach ($this->objects as $version) {
			$post = new Post($version->postID); 
			$thread = new Thread($post->threadID);
			
			if (!$thread->canEditPost($post)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	public function revert() {
                // handle attributes for logging
		foreach ($this->objects as $version) {
                    $post = new \wbb\data\post\PostAction(array(new Post($version->postID)), 'update', array(
                        'isEdit' => true, 
                        'data' => array(
                            'message' => $version->getMessage()
                        )
                    ));
                    $post->executeAction();
                }
	}
	
	/**
	 * Validate parameters to mark a version
	 */
	public function validateMarkVersions() {
		if (isset($this->parameters['version1'])) {
			$this->version1 = new PostHistoryVersion($this->parameters['version1']);
		}
                
		if ($this->version1 === null || !$this->version1->versionID) {
			throw new UserInputException('version1');
		}
		
		if (isset($this->parameters['version2'])) {
			$this->version2 = new PostHistoryVersion($this->parameters['version2']);
		}
                
		if ($this->version2 === null || !$this->version2->versionID || $this->version1->postID != $this->version2->postID) {
			throw new UserInputException('version2');
		}

		$post = new Post($this->version1->postID); 
		
		if (!$post->canRead()) {
			throw new PermissionDeniedException();
		}
		
		WCF::getSession()->checkPermissions(array('mod.board.canViewPostVersions'));
	}
	
	/**
	 * save the marked versions in the session
	 */
	public function markVersions() {
		$markedVersion = WCF::getSession()->getVar('edithistorymarkedversions');
		
		if ($markedVersion === null) {
			$markedVersion = array(); 
		}
		
		$markedVersion[$this->version1->postID]['one'] = $this->version1->versionID; 
		$markedVersion[$this->version1->postID]['second'] = $this->version2->versionID; 
		
		WCF::getSession()->register('edithistorymarkedversions', $markedVersion);
	}
	
	/**
	 * validate the parameters for validateGetMarkedVersions()
	 */
	public function validateGetMarkedVersions() {
		if (isset($this->parameters['postID'])) {
			$this->post = new Post($this->parameters['postID']);
		}
                
		if ($this->post === null || !$this->post->postID) {
			throw new UserInputException('postID');
		}
		
		if (!$this->post->canRead()) {
			throw new PermissionDeniedException();
		}
		
		WCF::getSession()->checkPermissions(array('mod.board.canViewPostVersions'));
	}
	
	/**
	 * return a array with the marked versions, if no data exist, the method return 0 for both versions
	 * @return array<int>
	 */
	public function getMarkedVersions() {
		$markedVersion = WCF::getSession()->getVar('edithistorymarkedversions');
		
		if (!isset($markedVersion[$this->post->postID])) {
			return array(
			    'one' => 0, 
			    'second' => 0
			);
		}
		
		return $markedVersion[$this->post->postID]; 
	}
	
        /**
	 * Validates parameters to return the logged ip addresses.
	 */
	public function validateGetIpLog() {
		if (!LOG_IP_ADDRESS) {
			throw new PermissionDeniedException();
		}
		
		if (isset($this->parameters['versionID'])) {
			$this->version = new PostHistoryVersion($this->parameters['versionID']);
		}
                
		if ($this->version === null || !$this->version->versionID) {
			throw new UserInputException('versionID');
		}
		
		$post = new Post($this->version->postID); 
		
		if (!$post->canRead()) {
			throw new PermissionDeniedException();
		}
		
		WCF::getSession()->checkPermissions(array('admin.user.canViewIpAddress', 'mod.board.canViewPostVersions'));
	}
	
	/**
	 * Returns a list of the logged ip addresses.
	 * 
	 * @return	array
	 */
	public function getIpLog() {
		$authorIpAddresses = Post::getIpAddressByAuthor($this->version->userID, $this->version->username, $this->version->ipAddress);
		
		// resolve hostnames
		$newIpAddresses = array();
		foreach ($authorIpAddresses as $ipAddress) {
			$ipAddress = UserUtil::convertIPv6To4($ipAddress);
			
			$newIpAddresses[] = array(
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress
			);
		}
		$authorIpAddresses = $newIpAddresses;
		
		// get other users of this ip address
		$otherUsers = array();
		if ($this->version->ipAddress) {
			$otherUsers = Post::getAuthorByIpAddress($this->version->ipAddress, $this->version->userID, $this->version->username);
		}
		
		$ipAddress = UserUtil::convertIPv6To4($this->version->ipAddress);
		
		if ($this->version->userID) {
			$sql = "SELECT	registrationIpAddress
				FROM	wcf".WCF_N."_user
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->version->userID
			));
			$row = $statement->fetchArray();
			
			if ($row !== false && $row['registrationIpAddress']) {
				$registrationIpAddress = UserUtil::convertIPv6To4($row['registrationIpAddress']);
				WCF::getTPL()->assign(array(
					'registrationIpAddress' => array(
						'hostname' => @gethostbyaddr($registrationIpAddress),
						'ipAddress' => $registrationIpAddress
					)
				));
			}
		}
		
		WCF::getTPL()->assign(array(
			'authorIpAddresses' => $authorIpAddresses,
			'ipAddress' => array(
				'hostname' => @gethostbyaddr($ipAddress),
				'ipAddress' => $ipAddress
			),
			'otherUsers' => $otherUsers,
                        // post is only used for $post->username so i can use the version
			'post' => $this->version
		));
		
		return array(
			'versionID' => $this->version->versionID,
			'template' => WCF::getTPL()->fetch('postIpAddress', 'wbb')
		);
	}
	
	public function validateCompare() {
		if (isset($this->parameters['version1'])) {
			$this->version1 = new PostHistoryVersion($this->parameters['version1']);
		}
                
		if ($this->version1 === null || !$this->version1->versionID) {
			throw new UserInputException('version1');
		}
		
		if (isset($this->parameters['version2'])) {
			$this->version2 = new PostHistoryVersion($this->parameters['version2']);
		}
                
		if ($this->version2 === null || !$this->version2->versionID || $this->version1->postID != $this->version2->postID) {
			throw new UserInputException('version2');
		}

		$post = new Post($this->version1->postID); 
		
		if (!$post->canRead()) {
			throw new PermissionDeniedException();
		}
		
		WCF::getSession()->checkPermissions(array('mod.board.canViewPostVersions'));
	}
	
	public function compare() {
		return array(
			'uid' => $this->version1->versionID.'y'.$this->version2->versionID,
			'template' => DiffUtil::toHTML(DiffUtil::compare($this->version1->message, $this->version2->message))
		);
	}
}
