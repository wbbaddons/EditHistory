/**
 * Namespace for user-related actions
 */
WBB.ACP.User = { };

/**
 * Handles the revert-edit clipboard action.
 */
WBB.ACP.User.EditHistory = {
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes WCF.ACP.User.EditHistory on first use.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind clipboard event listener
		$('.jsClipboardEditor').each($.proxy(function(index, container) {
			var $container = $(container);
			var $types = eval($container.data('types'));
			if (WCF.inArray('com.woltlab.wcf.user', $types)) {
				$container.on('clipboardAction', $.proxy(this._execute, this));
				return false;
			}
		}, this));
	},
	
	/**
	 * Handles clipboard actions.
	 * 
	 * @param	object		event
	 * @param	string		type
	 * @param	string		actionName
	 * @param	object		parameters
	 */
	_execute: function(event, type, actionName, parameters) {
		if (actionName == 'com.woltlab.wcf.user.revertLastEdits') {
			$('#wcfSystemConfirmation .buttonPrimary').on('click', function () {
				var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
				$notification.show();
			});
		}
	}
};
