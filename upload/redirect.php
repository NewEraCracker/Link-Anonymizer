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
	// Make safe for output
	$linkanonymizer_data = array();
	$linkanonymizer_data['bburl'] = htmlspecialchars_uni($mybb->settings['bburl']);
	$linkanonymizer_data['url']   = htmlspecialchars_uni($mybb->input['url']);
	$linkanonymizer_data['urljs'] = preg_replace_callback('|[^a-z0-9@#%*/+=.,:;_-]|i', create_function('$m', 'return "\\x" . @sprintf("%02x", ord($m[0]));'), $mybb->input['url']);

	// Load and parse language
	$lang->load('linkanonymizer');
	$lang->linkanonymizer_leaving = $lang->sprintf($lang->linkanonymizer_leaving, $mybb->settings['bbname'], $linkanonymizer_data['url']);

	// I love MyBB
	add_breadcrumb($lang->linkanonymizer);

	// Parse and output page
	eval('$linkanonymizer .= "'.$templates->get('linkanonymizer').'";');
	output_page($linkanonymizer);
}

die();
?>