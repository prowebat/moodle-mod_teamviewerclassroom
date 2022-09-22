<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/teamviewerclassroom/locallib.php');

/**
 * Define the complete choice structure for backup, with file and id annotations
 *
 * @package   mod_teamviewerclassroom
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_teamviewerclassroom_activity_structure_step extends backup_activity_structure_step {

	/**
	 * Define the structure for the teamviewerclassroom activity
	 * @return void
	 */
	protected function define_structure() {

		// To know if we are including userinfo.
		$userinfo = $this->get_setting_value('userinfo');
		$groupinfo = $this->get_setting_value('groups');

		// Define each element separated.
		$teamviewerclassroom = new backup_nested_element('teamviewerclassroom', array('id'),
			array('name',
				'intro',
				'introformat',
				'enable_autok_when_agent_leaves',
				'enable_waiting_room', 'enable_waiting_room_auto_join', 'timemodified',

				'openingtime', 'closingtime',
				// 'session_id': should be different for restored course
			));


		// Define sources.
		$teamviewerclassroom->set_source_table('teamviewerclassroom', array('course' => backup::VAR_COURSEID));

		// Return the root element (choice), wrapped into standard activity structure.

		return $this->prepare_activity_structure($teamviewerclassroom);
	}
}
