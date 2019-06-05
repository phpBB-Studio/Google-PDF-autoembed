<?php
/** @noinspection PhpUndefinedMethodInspection */

/**
 *
 * phpBB Studio - Google PDF autoembed. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\pdf;

/** @noinspection PhpUndefinedNamespaceInspection */

/**
 * phpBB Studio - Google PDF autoembed Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Check whether the extension can be enabled.
	 * Provides meaningful(s) error message(s) and the back-link on failure.
	 * CLI compatible (we do not use the $lang object here on purpose)
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		$is_enableable = true;

		$user = $this->container->get('user');
		$user->add_lang_ext('phpbbstudio/pdf', 'ext_require');
		$lang = $user->lang;

		if (!(phpbb_version_compare(PHPBB_VERSION, '3.2.7', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			/**
			 * Despite it seems wrong that's the right approach and not an error in coding.
			 * Done this way in order to avoid PHP errors like
			 * "Indirect modification of overloaded property phpbb/user::$lang has no effect"
			 * or " Can't use method return value in write context" depending on the use case.
			 * Discussed here: https://www.phpbb.com/community/viewtopic.php?p=14724151#p14724151
			*/
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('ERROR_PHPBB_VERSION', '3.2.7', '4.0.0@dev');

			$is_enableable = false;
		}

		if (!ini_get('allow_url_fopen'))
		{
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('ERROR_ALLOW_URL_FOPEN');
			$is_enableable = false;
		} 

		$user->lang = $lang;

		return $is_enableable;
	}
}
