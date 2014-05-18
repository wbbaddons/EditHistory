<?php
namespace wbb\data\post\history\version; 

use wbb\data\WBBDatabaseObject;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\bbcode\MessageParser;
use wcf\util\UserUtil; 

class PostHistoryVersion extends WBBDatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'post_history_version';

	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'versionID';
	
	private $user = null; 
        
        private $userprofile = null; 
	
	public function getUser() {
		if ($this->user === null) {
			$this->user = new User($this->userID); 
		}
		
		return $this->user; 
	}
        
        public function getUserProfile() {
		if ($this->userprofile === null) {
			$this->userprofile = new UserProfile($this->getUser()); 
		}
		
		return $this->userprofile; 
	}
	
	public function getFormattedMessage() {
		// assign embedded attachments
		AttachmentBBCode::setObjectID($this->postID);
		
		// parse and return message
		MessageParser::getInstance()->setOutputType('text/html');
		return MessageParser::getInstance()->parse($this->message, true, false, true);
	}
	
	public function getSimplifiedFormattedMessage() {
		MessageParser::getInstance()->setOutputType('text/simplified-html');
		return MessageParser::getInstance()->parse($this->message, true, false, true);
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function __toString() {
		return $this->getFormattedMessage();
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getUserID() {
		return $this->userID;
	}
	
	public function getTime() {
		return $this->time;
	}
	
	public function getIpAddress() {
		if ($this->ipAddress) {
			return UserUtil::convertIPv6To4($this->ipAddress);
		}
		
		return '';
        }
}