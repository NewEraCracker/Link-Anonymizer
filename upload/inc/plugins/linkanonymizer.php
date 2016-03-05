<?php
if(!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

$plugins->add_hook('parse_message_end', 'linkanonymizer_run');

function linkanonymizer_info()
{
	return array(
		'name'       => 'Link Anonymizer',
		'description'=> 'Anonymizes the links in all messages',
		'website'    => 'http://mods.mybb.com/view/link-anonymizer',
		'author'     => 'NewEraCracker',
		'authorsite' => '',
		'version'    => '1.5',
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
				$ignored_domains = array_merge($ignored_domains, array($mybb->settings['cookiedomain']));
			}

			$url = @parse_url($mybb->settings['bburl']);

			if($url !== false && !empty($url['host'])) {
				// Add forum domain to ignored domains
				$ignored_domains = array_merge($ignored_domains, array($url['host']));
			} else {
				// Bail out as we weren't able to fetch the required information
				$ignored_domains = null;
				return;
			}
		}

		$find_href = array();
		$repl_href = array();

		foreach($matches[1] as $rawurl) {
			$url = @parse_url($rawurl);

			if($url !== false && !empty($url['host'])) {
				$link_domain = strtolower($url['host']);

				// Ignore link if domain is whitelisted
				foreach($ignored_domains as $ignored_domain) {
					$ignored_domain = strtolower($ignored_domain);

					if(substr($ignored_domain, 0, 1) == '.') {
						if($ignored_domain == substr(".{$link_domain}", 0 - strlen($ignored_domain))) {
							continue 2;
						}
					} else if($ignored_domain == $link_domain) {
						continue 2;
					}
				}
			} else {
				continue;
			}

			// If we reach this, we must replace link
			$replacement = htmlspecialchars_uni($mybb->settings['bburl'].'/redirect.php?url='.urlencode(unhtmlentities($rawurl)));
			$find_href[] = "<a href=\"{$rawurl}\"";
			$repl_href[] = "<a href=\"{$replacement}\"";
		}

		if(sizeof($find_href)) {
			return str_replace($find_href, $repl_href, $message);
		}
	}
}
?>