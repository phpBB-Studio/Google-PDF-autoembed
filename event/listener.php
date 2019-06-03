<?php
/**
 *
 * phpBB Studio - Google PDF autoembed. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\pdf\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener.
 */
class listener implements EventSubscriberInterface
{
	/* @var \phpbb\language\language */
	protected $language;
	/* @var string phpBB root path */
	protected $root_path;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\language\language		$language		Language object
	 * @param  string						$root_path		phpBB root path
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\language\language $language, $root_path)
	{
		$this->language		= $language;
		$this->root_path	= $root_path;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup_after'							=> 'pdf_setup_lang',
			'core.parse_attachments_modify_template_data'	=> 'pdf_parse_attachment',
		];
	}

	/**
	 * Load extension language file during user set up.
	 *
	 * @event  core.user_setup_after
	 * @return void
	 * @access public
	 */
	public function pdf_setup_lang()
	{
		$this->language->add_lang('pdf_common', 'phpbbstudio/pdf');
	}

	/**
	 * Parse PDF attachment to be Google's readable
	 * Creates a copy on purpose.
	 * 
	 * @event  core.parse_attachments_modify_template_data
	 * @param \phpbb\event\data		$event
	 * @return void
	 * @access public
	 */
	public function pdf_parse_attachment($event)
	{
		$s_pdf_desc = false;

		if (@ini_get('allow_url_fopen'))
		{
			if ($event['attachment']['extension'] === 'pdf')
			{
				$copy_path = $this->root_path . 'images/pdf';
				$dest_file = $copy_path . '/' . utf8_basename($event['attachment']['real_filename']);
				$pdf_is_copy = (bool) @file_exists($dest_file);

				if (!$pdf_is_copy)
				{
					$this->make_pdf_dir($copy_path);

					$orig_file = $this->root_path . 'files/' . utf8_basename($event['attachment']['physical_filename']);

					$content = @file_get_contents($orig_file);

					if (!@file_put_contents($dest_file, $content))
					{
						$s_pdf_desc = true;
					}
				}

				$event['block_array'] = array_merge($event['block_array'], [
					'S_FILE'		=> false,
					'S_PDF_DESC'	=> (bool) $s_pdf_desc,
					'S_PDF'			=> $pdf_is_copy,
					'SRC'			=> generate_board_url() . '/images/pdf/' . utf8_basename($event['attachment']['real_filename']),
				]);
			}
		}
	}

	/**
	 * Create destination dir if doesn't exist
	 *
	 * @param string	$copy_path		Path to dir
	 * @return void
	 * @access protected
	 */
	protected function make_pdf_dir($copy_path)
	{
		if (!is_dir($copy_path))
		{
			@mkdir($copy_path, 0777);
		}

		if (!is_writable($copy_path))
		{
			@chmod($copy_path, 0777);
		}
	}
}
