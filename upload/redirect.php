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

/*
 Dirty hack
 @see http://dev.mybb.com/issues/409
 @see http://community.mybb.com/thread-43152.html
*/
$mybb->input['url'] = str_replace(dec_to_utf8(8203), '', $mybb->input['url']);

// Prevent search engines from even trying to index this page
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

// Redirect only if valid input is entered
if(preg_match('@^(http|https|ftp|news){1}://[^\n\r]+$@i', $mybb->input['url'])) {
	header('Location: ' . $mybb->input['url'], true, 303);
}

die();
?>