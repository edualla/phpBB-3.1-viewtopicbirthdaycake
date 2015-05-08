<?php
/**
*
* Birthday Cake extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\birthdaycake\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* the path to the images directory
	*
	*@var string
	*/
	protected $birthdaycake_path;

	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext,
		$birthdaycake_path)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->images_path = $birthdaycake_path;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'			=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',
			'core.viewtopic_before_f_read_check'		=> 'user_setup',
		);
	}

	/**
	* Set up the the lang vars
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function user_setup($event)
	{
		$this->user->add_lang_ext('rmcgirr83/birthdaycake', 'birthdaycake');
	}

	/**
	* Update viewtopic user data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_birthday'] = $event['row']['user_birthday'];
		$event['user_cache_data'] = $array;
	}

	/**
	* Update viewtopic guest data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_birthday'] = '';
		$event['user_cache_data'] = $array;
	}
	/**
	* Modify the viewtopic post row
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_modify_post_row($event)
	{
		$birthdaycake = $this->get_user_birthdaycake($event['user_poster_data']['user_birthday']);

		$event['post_row'] = array_merge($event['post_row'],array(
			'USER_BIRTHDAYCAKE' => $birthdaycake,
		));
	}

	/**
	 * Get user birthdaycake
	 *
	 * @param string $user_birthday User's Birthday
	 * @return string Zodiac image
	 */
	private function get_user_birthdaycake($user_birthday)
	{
		$birthdaycake = '';
		if (!empty($user_birthday))
		{
			$time = $this->user->create_datetime();
			$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

			list($bday, $bmonth) = array_map('intval', explode('-', $user_birthday));

			if ($bday === (int) $now['mday'] && $bmonth === (int) $now['mon'])
			{
				$birthdaycake = '<img src="' . $this->root_path . $this->images_path . 'icon_birthday.gif" alt="' . $this->user->lang['VIEWTOPIC_BIRTHDAY'] . '" title="' . $this->user->lang['VIEWTOPIC_BIRTHDAY'] . '"  style="vertical-align:middle;" />';
			}
		}
		return $birthdaycake;
	}
}
