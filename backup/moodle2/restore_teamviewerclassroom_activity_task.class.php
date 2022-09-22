<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/teamviewerclassroom/backup/moodle2/restore_teamviewerclassroom_stepslib.php');

/**
 * teamviewerclassroom restore task that provides all the settings and steps to perform one complete restore of the activity
 *
 * @package   mod_teamviewerclassroom
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_teamviewerclassroom_activity_task extends restore_activity_task {

	/**
	 * Define (add) particular settings this activity can have.
	 */
	protected function define_my_settings() {
		// No particular settings for this activity.
	}

	/**
	 * Define (add) particular steps this activity can have.
	 */
	protected function define_my_steps() {
		// Assignment only has one structure step.
		$this->add_step(new restore_teamviewerclassroom_activity_structure_step('teamviewerclassroom_structure', 'teamviewerclassroom.xml'));
	}

	/**
	 * Define the contents in the activity that must be
	 * processed by the link decoder.
	 *
	 * @return array
	 */
	static public function define_decode_contents() {
		$contents = array();

		$contents[] = new restore_decode_content('teamviewerclassroom', array('intro'), 'teamviewerclassroom');

		return $contents;
	}

	/**
	 * Define the decoding rules for links belonging
	 * to the activity to be executed by the link decoder.
	 *
	 * @return array of restore_decode_rule
	 */
	static public function define_decode_rules() {
		$rules = array();

		$rules[] = new restore_decode_rule('ASSIGNVIEWBYID',
			'/mod/teamviewerclassroom/view.php?id=$1',
			'course_module');
		$rules[] = new restore_decode_rule('ASSIGNINDEX',
			'/mod/teamviewerclassroom/index.php?id=$1',
			'course_module');

		return $rules;

	}

	/**
	 * Define the restore log rules that will be applied
	 * by the {@link restore_logs_processor} when restoring
	 * teamviewerclassroom logs. It must return one array
	 * of {@link restore_log_rule} objects.
	 *
	 * @return array of restore_log_rule
	 */
	static public function define_restore_log_rules() {
		$rules = array();

		$rules[] = new restore_log_rule('teamviewerclassroom', 'add', 'view.php?id={course_module}', '{teamviewerclassroom}');
		$rules[] = new restore_log_rule('teamviewerclassroom', 'update', 'view.php?id={course_module}', '{teamviewerclassroom}');
		$rules[] = new restore_log_rule('teamviewerclassroom', 'view', 'view.php?id={course_module}', '{teamviewerclassroom}');

		return $rules;
	}

	/**
	 * Define the restore log rules that will be applied
	 * by the {@link restore_logs_processor} when restoring
	 * course logs. It must return one array
	 * of {@link restore_log_rule} objects
	 *
	 * Note this rules are applied when restoring course logs
	 * by the restore final task, but are defined here at
	 * activity level. All them are rules not linked to any module instance (cmid = 0)
	 *
	 * @return array
	 */
	static public function define_restore_log_rules_for_course() {
		$rules = array();

		return $rules;
	}

	/**
	 * Given a comment area, return the itemname that contains the itemid mappings.
	 *
	 * @param string $commentarea
	 * @return string
	 */
	public function get_comment_mapping_itemname($commentarea) {
		switch ($commentarea) {
			case 'submission_comments':
				$itemname = 'submission';
				break;
			default:
				$itemname = parent::get_comment_mapping_itemname($commentarea);
				break;
		}

		return $itemname;
	}
}
