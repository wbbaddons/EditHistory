<?php
namespace wbb\page;

use wcf\page\MultipleLinkPage; 
use wbb\data\post\Post; 
use wbb\data\thread\ViewableThread;
use wcf\system\WCF; 
use wbb\system\WBBCore; 
use wbb\data\board\BoardCache; 
use wbb\data\thread\Thread; 
use wcf\system\exception\IllegalLinkException; 

/**
 * Shows the diffrent versions of a posting
 * 
 * @author	Joshua Rüsweg
 * @package	de.joshsboard.edithistory
 * @subpackage	page
 * @category	Burning Board
 */
class PostVersionsPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wbb.header.menu.board';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = WBB_THREAD_POSTS_PER_PAGE;
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'DESC';
	
        
	public $sortField = 'post_history_version.time';
	
	/**
	 * @see	\wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * thread id
	 * @var	integer
	 */
	public $postID = 0;
	
	/**
	 * post object
	 * @var	\wbb\data\post\Post
	 */
	public $post = null;
	
	/**
	 * thread object
	 * @var	\wbb\data\thread\Thread
	 */
	public $thread = null;
	
	public $objectListClassName = 'wbb\data\post\history\version\PostHistoryVersionList';
	
        public $board = null; 
        
	public $canEdit = false; 
	
	public $neededPermissions = array('mod.board.canViewPostVersions'); 
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->postID = intval($_REQUEST['id']);
	
		if ($this->postID) {
			$this->post = new Post($this->postID);
			if (!$this->post->postID) {
				throw new IllegalLinkException();
			}
			$this->threadID = $this->post->threadID;
		}
		
		$this->thread = ViewableThread::getThread($this->threadID);
		
		if (!$this->thread->canRead()) {
			throw new PermissionDeniedException();
		}
                
                $this->board = BoardCache::getInstance()->getBoard($this->thread->boardID);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
                
		if ($this->countItems() == 0) {
			throw new IllegalLinkException(); 
		}
		
		WBBCore::getInstance()->setBreadcrumbs($this->board->getParentBoards(), $this->board, new Thread($this->thread->threadID));
	}
	
	public function initObjectList() {
		parent::initObjectList(); 
		
		$this->objectList->getConditionBuilder()->add('post_history_version.postID = ?', array($this->postID)); 
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'isLastPage' => $this->isLastPage(),
			'sortOrder' => $this->sortOrder,
			'post' => $this->post,
			'allowSpidersToIndexThisPage' => true,
			'permissionCanUseSmilies' => 'user.message.canUseSmilies', 
			'canRestore' => $this->thread->canEditPost($this->post)
		));
	}
}
