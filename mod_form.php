<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_teamviewerclassroom_mod_form extends moodleform_mod {

	/**
	 * Called to define this moodle form
	 *
	 * @return void
	 */
	public function definition() {
		global $CFG, $DB, $OUTPUT, $PAGE;
		$mform = &$this->_form;
		$activity = $this->current;

		$mform->addElement('header', 'general', teamviewerclassroom_get_string('mod_form_header_general'));
		$mform->addElement('text', 'name', teamviewerclassroom_get_string('mod_form_name'), 'maxlength="64" size="32"');
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');

		$mform->addElement('textarea', 'intro', teamviewerclassroom_get_string('mod_form_intro'), array('rows' => 3, 'cols' => 60));
		$mform->setType('introeditor', PARAM_TEXT);

		$mform->addElement('checkbox', 'enable_waiting_room',
			teamviewerclassroom_get_string('mod_form_enable_waiting_room'),
			teamviewerclassroom_get_string('mod_form_enable_waiting_room_description'));
		$mform->setDefault('enable_waiting_room', 1);
		$mform->setType('enable_waiting_room', PARAM_INT);

		$mform->addElement('checkbox', 'enable_waiting_room_auto_join',
			teamviewerclassroom_get_string('mod_form_enable_waiting_room_auto_join'),
			teamviewerclassroom_get_string('mod_form_enable_waiting_room_auto_join_description'));
		$mform->setDefault('enable_waiting_room_auto_join', 1);
		$mform->setType('enable_waiting_room_auto_join', PARAM_INT);

		// disabled for now:
		// $mform->addElement('checkbox', 'enable_autok_when_agent_leaves',
		// 	teamviewerclassroom_get_string('mod_form_enable_autok_when_agent_leaves'),
		// 	teamviewerclassroom_get_string('mod_form_enable_autok_when_agent_leaves_description'));
		// $mform->setDefault('enable_autok_when_agent_leaves', 1);
		// $mform->setType('enable_autok_when_agent_leaves', PARAM_INT);

		// $this->standard_intro_elements();
		// $element = $mform->getElement('introeditor');
		// $attributes = $element->getAttributes();
		// $attributes['rows'] = 3;
		// $element->setAttributes($attributes);

		$mform->addElement('header', 'schedule', teamviewerclassroom_get_string('mod_form_block_schedule'));
		if (isset($activity->openingtime) && $activity->openingtime != 0 ||
			isset($activity->closingtime) && $activity->closingtime != 0) {
			$mform->setExpanded('schedule');
		}
		$mform->addElement('date_time_selector', 'openingtime',
			teamviewerclassroom_get_string('mod_form_field_openingtime'), array('optional' => true));
		$mform->setDefault('openingtime', 0);
		$mform->addElement('date_time_selector', 'closingtime',
			teamviewerclassroom_get_string('mod_form_field_closingtime'), array('optional' => true));
		$mform->setDefault('closingtime', 0);


		$this->standard_coursemodule_elements();
		// Add standard buttons, common to all modules.
		$this->add_action_buttons();
	}


	/**
	 * Validates the data processed by the form.
	 *
	 * @param array $data
	 * @param array $files
	 * @return void
	 */
	public function validation($data, $files) {
		$errors = parent::validation($data, $files);

		if (isset($data['openingtime']) && isset($data['closingtime'])) {
			if ($data['openingtime'] != 0 && $data['closingtime'] != 0 &&
				$data['closingtime'] < $data['openingtime']) {
				$errors['closingtime'] = teamviewerclassroom_get_string('mod_form_duetimeoverstartingtime');
			}
		}

		return $errors;
	}
}
