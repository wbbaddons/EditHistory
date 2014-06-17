<?php
/**
 * so that not all versions are deleted directly through the cronjob
 */
$stmt = \wcf\system\WCF::getDB()->prepareStatement("UPDATE wbb". WCF_N ."_post_history_version SET created = ?");
$stmt->execute(array(TIME_NOW));