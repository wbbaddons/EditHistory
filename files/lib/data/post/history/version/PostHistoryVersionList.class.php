<?php
namespace wbb\data\post\history\version; 

use wcf\data\DatabaseObjectList;

class PostHistoryVersionList extends DatabaseObjectList {

	/**
	 * @see	wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = "post_history_version.versionID ASC";

}