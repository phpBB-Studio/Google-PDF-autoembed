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
	'ERROR_PHPBB_VERSION'		=> 'Minimum phpBB version required is 3.2.7 but less than 4.0.0@dev',
	'ERROR_PHP_VERSION_RHEA'	=> 'Minimum PHP version required is 5.4.7',
	'ERROR_PHP_VERSION_OBERON'	=> 'Minimum PHP version required is 7.1',
	'ERROR_ALLOW_URL_FOPEN'		=> 'The “allow_url_fopen” PHP directive must be enabled server side!',
]);
