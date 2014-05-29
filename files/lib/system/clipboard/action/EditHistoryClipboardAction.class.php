<?php
namespace wbb\system\clipboard\action;

use wcf\system\clipboard\action\UserClipboardAction;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for versions
 * 
 * @author	Joshua Rüsweg
 * @package	de.joshsboard.edithistory
 * @subpackage	system.clipboard.action
 * @category	Burning Board
 */
class EditHistoryClipboardAction extends UserClipboardAction {

	/**
	 * @see	\wcf\system\clipboard\action\AbstractClipboardAction::$actionClassActions
	 */
	protected $actionClassActions = array('revertLastEdits');
	
	/**
	 * @see	wcf\system\clipboard\action\AbstractClipboardAction::$supportedActions
	 */
	protected $supportedActions = array('revertLastEdits');
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::execute()
	 */
	public function execute(array $objects, \wcf\data\clipboard\action\ClipboardAction $action) {
		$item = parent::execute($objects, $action);
		
		if ($item === null) {
			return null;
		}

		// handle actions
		switch ($action->actionName) {
		
			case 'revertLastEdits':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.user.revertLastEdits.confirmMessage', array(
					'count' => $item->getCount()
				)));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getClassName()
	 */
	public function getClassName() {
		return 'wbb\data\user\EditHistoryUserAction';
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getTypeName()
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.user';
	}
	
	/**
	 * Returns the ids of the users which can be deleted.
	 * 
	 * @return	array<integer>
	 */
	protected function validateRevertLastEdits() {
		return $this->__validateAccessibleGroups(array_keys($this->objects)); // ignore own
	}
}
