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
		'website'    => 'https://github.com/NewEraCracker/Link-Anonymizer',
		'author'     => 'NewEraCracker',
		'authorsite' => 'https://github.com/NewEraCracker',
		'version'    => '1.8.0',
		'guid'       => 'ef3f9596c24e4d7ca4f364f74c2fd12e'
	);
}

function linkanonymizer_activate()
{
	global $db;
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

	$templates = array();
	$templates[] = array(
		'title' => 'linkanonymizer',
		'template' => "<html>
	<head>
		<title>{\$lang->linkanonymizer}</title>
		{\$headerinclude}
	</head>
	<body>
		{\$header}
		<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"tborder\">
			<tr>
				<td class=\"thead\" colspan=\"2\"><strong>{\$lang->linkanonymizer_redirecting}</strong></td>
			</tr>
			<tr>
				<td colspan=\"2\">{\$lang->linkanonymizer_leaving}<br />{\$lang->linkanonymizer_ownrisk}</td>
			</tr>
			<tr class=\"tcat\">
				<td style=\"text-align:right;width:50%\">
					<strong><a href=\"{\$mybb->settings['bburl']}\">{\$lang->linkanonymizer_cancel}</a></strong>
				</td>
				<td style=\"text-align:left;width:50%\">
					<strong><a href=\"{\$mybb->input['url']}\">{\$lang->linkanonymizer_continue} (<span class=\"delay\">5</span>)</a></strong>
				</td>
			</tr>
		</table>
		{\$footer}
		<script>
			<!--
				var delayCount = parseInt(\"5\") + 1,
				countdown = function()
				{
					if (--delayCount > -1)
					{
						\$('.delay').text(delayCount);
						setTimeout(countdown, 1000);
					}
					else
					{
						window.open(\"{\$mybb->input['url']}\", \"_parent\");
					}
				}
				\$(document).ready(countdown);
			//-->
		</script>
	</body>
</html>"
	);
	foreach($templates as $template)
	{
		$insert = array(
			'title' => $db->escape_string($template['title']),
			'template' => $db->escape_string($template['template']),
			'sid' => '-1',
			'version' => '1800',
			'dateline' => TIME_NOW
		);
		$db->insert_query('templates', $insert);
	}
}

function linkanonymizer_deactivate()
{
	global $db;
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

	$templates = array(
		'linkanonymizer'
	);
	$templates = "'" . implode("','", $templates) . "'";
	$db->delete_query('templates', "title IN ({$templates})");
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