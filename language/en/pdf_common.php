<?php
/**
 *
 * phpBB Studio - Google PDF autoembed. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/*
 * Some characters you may want to copy&paste:
 * ’ » “ ” …
 */
$lang = array_merge($lang, [
	'PDF_ERROR_FILE_SYSTEM'	=> '<em>Something went wrong moving data!</em><br><em>Unable to convert to embedded PDF.</em>',
]);
