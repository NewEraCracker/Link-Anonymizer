<?php
/**
 * Link Anonymizer
 *
 * Author: NewEraCracker
 * License: Public Domain
 *
 * https://github.com/NewEraCracker/Link-Anonymizer
 */
if(defined('IN_MYBB')) {
	die('This file is not meant to be run from MyBB.');
} else {
	define('IN_MYBB', 1);
	require_once('./global.php');
}

// Send Cache-Control and Pragma headers to disable caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0', true);
header('Pragma: no-cache', true);

// Prevent search engines from even trying to index this page
if($session->is_spider) {
	header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true, 403);
	$mybb->input['ajax'] = true; error_no_permission(); exit();
}
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

/*
 Dirty hack
 @see http://dev.mybb.com/issues/409
 @see http://community.mybb.com/thread-43152.html
*/
$mybb->input['url'] = str_replace(dec_to_utf8(8203), '', (string)$mybb->input['url']);

// Redirect only if valid input is entered
$matches = array();
if(preg_match('@^(http|https|ftp|news){1}://[^\x00-\x1f\x7f]+$@i', $mybb->input['url'], $matches)) {
	header('Location: ' . $matches[0], true, 303);
} else {
	header('Location: ' . $mybb->settings['bburl'] . '/', true, 303);
}
die();
?>