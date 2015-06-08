<?php
if(defined('IN_MYBB')) {
	die('This file is not meant to be run from MyBB.');
} else {
	define('IN_MYBB', 1);
	require_once './global.php';
}

/*
 Dirty hack
 @see http://dev.mybb.com/issues/409
 @see http://community.mybb.com/thread-43152.html
*/
$mybb->input['url'] = str_replace(dec_to_utf8(8203), '', $mybb->input['url']);

if(preg_match('/^(http|https|ftp|news){1}:\/\/[^\n\r]*$/', $mybb->input['url'])) {
	header('Location: ' . $mybb->input['url']);
}

die();
?>