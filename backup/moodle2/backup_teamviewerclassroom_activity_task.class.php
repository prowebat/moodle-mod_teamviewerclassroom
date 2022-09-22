<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/teamviewerclassroom/backup/moodle2/backup_teamviewerclassroom_stepslib.php');

/**
 * teamviewerclassroom backup task that provides all the settings and steps to perform one complete backup of the activity
 *
 * @package   mod_teamviewerclassroom
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_teamviewerclassroom_activity_task extends backup_activity_task {

	/**
	 * Define (add) particular settings this activity can have
	 */
	protected function define_my_settings() {
		// No particular settings for this activity.
	}

	/**
	 * Define (add) particular steps this activity can have
	 */
	protected function define_my_steps() {
		$this->add_step(new backup_teamviewerclassroom_activity_structure_step('teamviewerclassroom_structure', 'teamviewerclassroom.xml'));
	}

	/**
	 * Code the transformations to perform in the activity in
	 * order to get transportable (encoded) links
	 * @param string $content
	 * @return string
	 */
	static public function encode_content_links($content) {
		return $content;
	}

}

