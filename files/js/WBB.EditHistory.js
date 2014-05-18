WBB.EditHistory = {};

WBB.EditHistory.Revert = Class.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Creates a new object of this class.
	 */
	init: function() {
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});

		this._initButtons();

	},
		
	/**
	 * Initializes the button events.
	 */
	_initButtons: function() {
		var self = this;
		$('.jsPostRevert').each(function(index, button) {
			var $button = $(button);
			
			$button.click($.proxy(self._click, self));
		});
	},
		
	/**
	 * Sends request after clicking on a button.
	 */
	_click: function(event) {
                var $versionID = $(event.currentTarget).data('versionID');
                
		this._proxy.setOption('data', {
			actionName: 'revert',
			className: 'wbb\\data\\post\\history\\version\\PostHistoryVersionAction',
			objectIDs: [$versionID]
		});

		this._proxy.sendRequest();
	},
	/**
	 * Shows a notification on success.
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		location.reload();

		var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
		$notification.show();
	}
});

/**
 * Displays an overlay for post ips.
 */
WBB.EditHistory.IPAddressHandler = Class.extend({
	/**
	 * template cache
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * dialog object
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the post ip overlay.
	 */
	init: function() {
		this._cache = { };
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initButtons();
		
		WCF.DOMNodeInsertedHandler.addCallback('WBB.EditHistory.IPAddressHandler', $.proxy(this._initButtons, this));
	},
	
	/**
	 * Initializes the button events.
	 */
	_initButtons: function() {
		var self = this;
		$('.jsIpAddress').each(function(index, button) {
			var $button = $(button);
			var $versionID = $button.data('versionID');
			
			if (self._cache[$versionID] === undefined) {
				self._cache[$versionID] = '';
				$button.click($.proxy(self._click, self));
			}
		});
	},
	
	/**
	 * Handles clicks on the show ip button.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		var $versionID = $(event.currentTarget).data('versionID');
		
		if (this._cache[$versionID]) {
			this._showDialog($versionID);
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'getIpLog',
				className: 'wbb\\data\\post\\history\\version\\PostHistoryVersionAction',
				parameters: {
					versionID: $versionID
				}
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		// cache template
		this._cache[data.returnValues.versionID] = data.returnValues.template;
		
		// show dialog
		this._showDialog(data.returnValues.versionID);
	},
	
	/**
	 * Shows the overlay for given version id.
	 * 
	 * @param	integer		versionID
	 */
	_showDialog: function(versionID) {
		if (this._dialog === null) {
			this._dialog = $('<div id="wbbIpAddressLog" />').hide().appendTo(document.body);
		}
		
		this._dialog.html(this._cache[versionID]);
		this._dialog.wcfDialog({
			title: WCF.Language.get('wbb.post.ipAddress.title')
		});
		this._dialog.wcfDialog('render');
	}
});
