<?php
/**
 * Link Anonymizer
 *
 * Author: NewEraCracker
 * License: Public Domain
 *
 * https://github.com/NewEraCracker/Link-Anonymizer
 */
if(!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

$plugins->add_hook('build_friendly_wol_location_end', 'linkanonymizer_hide');
$plugins->add_hook('parse_message_end', 'linkanonymizer_run');

function linkanonymizer_info()
{
	return array(
		'name'       => 'Link Anonymizer',
		'description'=> 'Anonymizes the links in all messages',
		'website'    => 'https://github.com/NewEraCracker/Link-Anonymizer',
		'author'     => 'NewEraCracker',
		'authorsite' => 'https://github.com/NewEraCracker',
		'version'    => '1.5.4',
		'guid'       => 'ef3f9596c24e4d7ca4f364f74c2fd12e'
	);
}

function linkanonymizer_activate()
{
	// Intentionally left empty
}

function linkanonymizer_deactivate()
{
	// Intentionally left empty
}

function linkanonymizer_hide(&$plugin_array)
{
	global $mybb, $lang;

	if(!is_array($plugin_array) || !isset($plugin_array['user_activity'], $plugin_array['user_activity']['location'])) {
		// This should never happen, but better play safe so it doesn't break in case API changes
		return;
	}

	if(empty($plugin_array['location_name']) && strpos($plugin_array['user_activity']['location'], "/redirect.php?") !== false) {
		// Protect users privacy by concealing their true location
		$plugin_array['location_name'] = $lang->sprintf($lang->unknown_location, $mybb->settings['bburl']);
	}
}

function linkanonymizer_run($message)
{
	global $mybb;
	static $ignored_domains;

	if(preg_match_all('@<a href="([^"]+)@i', $message, $matches)) {
		// Build ignored domains only once
		if(!is_array($ignored_domains)) {
			$ignored_domains = array();

			if($mybb->settings['cookiedomain']) {
				// Add cookie domain to ignored domains
				$ignored_domains[] = strtolower($mybb->settings['cookiedomain']);
			}

			$bbhost = @parse_url($mybb->settings['bburl'], PHP_URL_HOST);

			if(empty($bbhost)) {
				// Bail out as we weren't able to fetch the required information
				$ignored_domains = null;
				return;
			}

			// Add forum domain to ignored domains
			$ignored_domains[] = strtolower($bbhost);
		}

		$find_href = $repl_href = array();

		foreach($matches[1] as $rawurl) {
			$link_domain = @parse_url($rawurl, PHP_URL_HOST);

			if(empty($link_domain)) {
				continue;
			}

			$link_domain = strtolower($link_domain);

			// Ignore link if domain is whitelisted
			foreach($ignored_domains as $ignored_domain) {
				if(substr($ignored_domain, 0, 1) == '.') {
					if($ignored_domain == substr(".{$link_domain}", (0 - strlen($ignored_domain)))) {
						continue 2;
					}
				} elseif($ignored_domain == $link_domain) {
					continue 2;
				}
			}

			// If we reach this, we must replace link
			$replacement = htmlspecialchars_uni($mybb->settings['bburl'].'/redirect.php?url='.urlencode(unhtmlentities($rawurl)));
			$find_href[] = "<a href=\"{$rawurl}\"";
			$repl_href[] = "<a href=\"{$replacement}\"";
		}

		if(count($find_href)) {
			return str_replace($find_href, $repl_href, $message);
		}
	}
}
?>