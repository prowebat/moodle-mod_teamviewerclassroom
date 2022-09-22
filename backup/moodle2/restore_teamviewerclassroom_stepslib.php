<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/teamviewerclassroom/locallib.php');

/**
 * Define the complete classroom structure for restore, with file and id annotations
 *
 * @package   mod_teamviewerclassroom
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_teamviewerclassroom_activity_structure_step extends restore_activity_structure_step {

	/**
	 * Define the structure of the restore workflow.
	 *
	 * @return restore_path_element $structure
	 */
	protected function define_structure() {

		$paths = array();
		// To know if we are including userinfo.
		$userinfo = $this->get_setting_value('userinfo');

		// Define each element separated.
		$paths[] = new restore_path_element('teamviewerclassroom', '/activity/teamviewerclassroom');

		return $this->prepare_activity_structure($paths);
	}

	/**
	 * Process an teamviewerclassroom restore.
	 *
	 * @param object $data The data in object form
	 * @return void
	 */
	protected function process_teamviewerclassroom($data) {
		global $DB;

		$data = (object)$data;
		$data->course = $this->get_courseid();

		$DB->delete_records('teamviewerclassroom', ['course' => $data->course]);
		$newitemid = $DB->insert_record('teamviewerclassroom', $data);

		$this->apply_activity_instance($newitemid);
	}
}
