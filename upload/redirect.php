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
$mybb->input['url'] = str_replace(dec_to_utf8(8203), '', (string)$mybb->input['url']);

// Prevent search engines from even trying to index this page
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

// Redirect only if valid input is entered
$matches = array();
if(preg_match('@^(http|https|ftp|news){1}://[^\x00-\x1f\x7f]+$@i', $mybb->input['url'], $matches)) {
	// Make safe for output
	$linkanonymizer_data = array();
	$linkanonymizer_data['bburl'] = htmlspecialchars_uni($mybb->settings['bburl']);
	$linkanonymizer_data['url']   = htmlspecialchars_uni($matches[0]);
	$linkanonymizer_data['urljs'] = preg_replace_callback('|[^a-z0-9@#%*/+=.,:;_-]|i', create_function('$m', 'return "\\x" . @sprintf("%02x", ord($m[0]));'), $matches[0]);

	// Load and parse language
	$lang->load('linkanonymizer');
	$lang->linkanonymizer_leaving = $lang->sprintf($lang->linkanonymizer_leaving, $mybb->settings['bbname'], $linkanonymizer_data['url']);

	// I love MyBB
	add_breadcrumb($lang->linkanonymizer);

	// Parse and output page
	eval('$linkanonymizer .= "'.$templates->get('linkanonymizer').'";');
	output_page($linkanonymizer);
} else {
	// Make safe for output
	$linkanonymizer_data = array();
	$linkanonymizer_data['bburl'] = htmlspecialchars_uni($mybb->settings['bburl']);

	// A friendly error page
	$lang->load('linkanonymizer');
	$lang->linkanonymizer_error = $lang->sprintf($lang->linkanonymizer_error, "<br /><b><a href=\"{$linkanonymizer_data['bburl']}\">", '</a></b>');
	error($lang->linkanonymizer_error, $lang->linkanonymizer);
}
die();
?>