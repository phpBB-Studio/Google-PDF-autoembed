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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * phpBB Studio - Google PDF autoembed Listener.
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\language\language */
	protected $language;

	/** @var string phpBB attachments table */
	protected $attachments_table;

	/* @var string phpBB root path */
	protected $root_path;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\db\driver\driver_interface	$db					Database object
	 * @param  \phpbb\language\language				$language			Language object
	 * @param  string								$attachments_table	phpBB attachments table
	 * @param  string								$root_path			phpBB root path
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		$attachments_table,
		$root_path)
	{
		$this->db					= $db;
		$this->language				= $language;

		$this->attachments_table	= $attachments_table;
		$this->root_path			= $root_path;
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
			'core.delete_attachments_collect_data_before'	=> 'pdf_delete_attachment',
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
	 * Parse PDF attachment to be Google readable.
	 * Creates a copy on purpose.
	 *
	 * @event  core.parse_attachments_modify_template_data
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function pdf_parse_attachment($event)
	{
		/** We do not accept embedding previews to avoid create the mirror file in advance */
		if (@ini_get('allow_url_fopen') && !$event['preview'])
		{
			if ($event['attachment']['extension'] === 'pdf')
			{
				$pdf_owner = (int) $event['attachment']['poster_id'];

				$copy_path	= $this->root_path . 'images/pdf/' . $pdf_owner;
				$dest_file	= $copy_path . '/' . utf8_basename($event['attachment']['physical_filename'] . '_' . $event['attachment']['real_filename']);
				$s_pdf_copy	= (bool) @file_exists($dest_file);
				$s_pdf_desc	= false;

				if (!$s_pdf_copy)
				{
					$this->make_pdf_dir($copy_path);

					$orig_file = $this->root_path . 'files/' . utf8_basename($event['attachment']['physical_filename']);

					$content = @file_get_contents($orig_file);

					if (!@file_put_contents($dest_file, $content))
					{
						$s_pdf_desc = true;
					}
				}

				$u_pdf_url = generate_board_url() . '/images/pdf/' . $pdf_owner . '/' . utf8_basename(rawurlencode($event['attachment']['physical_filename'] . '_' . $event['attachment']['real_filename']));

				$event['block_array'] = array_merge($event['block_array'], [
					'S_FILE'		=> false,
					'S_PDF_DESC'	=> (bool) $s_pdf_desc,
					'S_PDF'			=> true,
					'SRC'			=> (string) $u_pdf_url,
				]);
			}
		}
	}

	/**
	 * Delete any files in the PDF directory, when the respective attachment is deleted.
	 *
	 * @event  core.delete_attachments_collect_data_before
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function pdf_delete_attachment($event)
	{
		$data = $this->pdf_attachments($event['ids'], $event['sql_id']);

		foreach ($data as $row)
		{
			$this->pdf_delete((int) $row['poster_id'], (string) $row['physical_filename'], (string) $row['real_filename']);
		}
	}

	/**
	 * Create destination dir if doesn't exist.
	 *
	 * @param  string	$copy_path		Path to directory
	 * @return void
	 * @access protected
	 */
	protected function make_pdf_dir($copy_path)
	{
		if (!is_dir($copy_path))
		{
			@mkdir($copy_path, 0777, true);
		}

		if (!is_writable($copy_path))
		{
			@chmod($copy_path, 0777);
		}

		$pdf_index = $this->root_path . 'ext/phpbbstudio/pdf/docs/index.html';

		if (@file_exists($pdf_index))
		{
			if (!@file_exists($copy_path . '/index.html'))
			{
				@copy(
					$pdf_index,
					$copy_path . '/index.html'
				);
			}

			if (!@file_exists($this->root_path . 'images/pdf/index.html'))
			{
				@copy(
					$pdf_index,
					$this->root_path . 'images/pdf/index.html'
				);
			}
		}
	}

	/**
	 * Retrieve data for multiple attachments.
	 *
	 * @param  mixed	$ids		The attachment identifier
	 * @param  string	$column		The identifier column
	 * @return array
	 * @access protected
	 */
	protected function pdf_attachments($ids, $column = 'attach_id')
	{
		$data = [];

		$sql = 'SELECT attach_id, poster_id, physical_filename, real_filename
				FROM ' . $this->attachments_table . '
				WHERE ' . $this->db->sql_in_set($column, (array) $ids);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data[(int) $row['attach_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return (array) $data;
	}

	/**
	 * Deletes a file from the PDF directory.
	 *
	 * @param  int		$user_id		The poster's user identifier.
	 * @param  string	$file_physical	The attachment's physical filename
	 * @param  string	$file_real		The attachment's real filename
	 * @return void
	 * @access protected
	 */
	protected function pdf_delete($user_id, $file_physical, $file_real)
	{
		$target = $this->root_path . '/images/pdf/' . $user_id . '/' . $file_physical . '_' . $file_real;

		if (@file_exists($target))
		{
			@unlink($target);
		}
	}
}
