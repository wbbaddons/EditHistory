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

/**
 * Displays an overlay for post compares. 
 */
WBB.EditHistory.CompareHandler = Class.extend({
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
	 * version id
	 * @var	int
	 */
	_cversion1: 0, 
	
	/**
	 * version id
	 * @var int
	 */
	_cversion2: 0, 
	
	/**
	 * post id
	 * @var int
	 */
	_postID: 0,
	
	/**
	 * Initializes the post ip overlay.
	 */
	init: function(postID) {
		this._postID = postID; 
		this._cache = { };
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._initButtons();
		
		WCF.DOMNodeInsertedHandler.addCallback('WBB.EditHistory.CompareHandler', $.proxy(this._initButtons, this));
		
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'getMarkedVersions',
				className: 'wbb\\data\\post\\history\\version\\PostHistoryVersionAction',
				parameters: {
					postID: this._postID // @TODO
				}
			},
			success: $.proxy(this._loadMarkedVersionsSuccess, this)
		});
	},
	
	/**
	 * Handles clicks on the compare button.
	 * 
	 * @param	object		event
	 */
	_compare: function() {
		if (this._cache[this._createuid(this._cversion1, this._cversion2)] != undefined) {
			this._showDialog(this._createuid(this._cversion1, this._cversion2));
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'compare',
				className: 'wbb\\data\\post\\history\\version\\PostHistoryVersionAction',
				parameters: {
					version1: this._cversion1, 
					version2: this._cversion2
				}
			});
			this._proxy.sendRequest();
		}
	}, 
	
	/**
	 * Initializes the button events.
	 */
	_initButtons: function() {
		$('<div id="showQuotes" class="balloonTooltip" />').click($.proxy(this._compare, this)).text(WCF.Language.get('wbb.post.edithistory.compare')).appendTo(document.body).show();
		
		var self = this; 
		
		$('.firstVersionCompare').each(function(index, button) {
			var $button = $(button);
			
			$button.change(function () {
				self._cversion1 = $(this).data('objectId');
				self._regenerateOptions(); 
			});
		});
		
		$('.secondVersionCompare').each(function(index, button) {
			var $button = $(button);
			
			$button.change(function () {
				self._cversion2 = $(this).data('objectId');
				self._regenerateOptions();
			});
		});
	},
	
	/**
	 * set the marked versions
	 */
	_loadMarkedVersionsSuccess: function(data, textStatus, jqXHR) {
		if (data.returnValues.one !== 0 && data.returnValues.second !== 0) { // if only one is != 0; the data is invalid
			console.log("loaded from session");
			
			this._cversion1 = data.returnValues.one; 
			this._cversion2 = data.returnValues.second; 
			
			
			console.log(this._cversion1);
			console.log(this._cversion2);
			
			console.log(data); 
			
			$('#radioButtonVersionOne' + this._cversion1).attr('checked', 'checked');
			$('#radioButtonVersionSecond' + this._cversion2).attr('checked', 'checked');
		} else {
			$('.firstVersionCompare').first().attr('checked', 'checked');
			$('.secondVersionCompare').last().attr('checked', 'checked');

			this._cversion1 = $('.firstVersionCompare').first().data('objectId');
			this._cversion2 = $('.secondVersionCompare').last().data('objectId');

			this._regenerateOptions();
		}
	}, 
	
	/**
	 * save the marked versions in the session
	 */
	_saveMarkedVersions: function() {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'markVersions',
				className: 'wbb\\data\\post\\history\\version\\PostHistoryVersionAction',
				parameters: {
					version1: this._cversion1, 
					version2: this._cversion2
				}
			}
		});
	},
	
	/**
	 * recalculate the disabled options
	 */
	_regenerateOptions: function() {
		var self = this; 
		
		$('.secondVersionCompare').each(function(index, button) {
			var $button = $(button);
			
			if ($(button).data('objectId') >= self._cversion1) {
				$(button).prop('disabled', true); 
			} else {
				$(button).prop('disabled', false); 
			}
		});
		
		$('.firstVersionCompare').each(function(index, button) {
			var $button = $(button);
			
			if ($(button).data('objectId') <= self._cversion2) {
				$(button).prop('disabled', true); 
			} else {
				$(button).prop('disabled', false); 
			}
		});
		
		// save versions in session
		this._saveMarkedVersions(); 
	}, 
	
	/**
	 * create a uid for the diff
	 */
	_createuid: function(version1, version2) {
		return version1+'y'+version2; 
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
		this._cache[data.returnValues.uid] = data.returnValues.template;
		
		// show dialog
		this._showDialog(data.returnValues.uid);
	},
	
	/**
	 * Shows the overlay for given version id.
	 * 
	 * @param	integer		versionID
	 */
	_showDialog: function(uid) {
		if (this._dialog === null) {
			this._dialog = $('<div id="wbbEditHistoryCompare" />').hide().appendTo(document.body);
		}
		
		this._dialog.html(this._cache[uid]);
		this._dialog.wcfDialog({
			title: WCF.Language.get('wbb.post.edithistory.comparison')
		});
		this._dialog.wcfDialog('render');
	}
});