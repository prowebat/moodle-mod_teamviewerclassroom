<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/lib.php');

if ($ADMIN->fulltree) {
	$settings->add(new admin_setting_configtext('teamviewerclassroom/server_url',
		teamviewerclassroom_get_string('setting_server_url'),
		teamviewerclassroom_get_string('setting_server_url_description'),
		'https://de01.classroom.teamviewer.com', PARAM_TEXT));

	$settings->add(new admin_setting_configtext('teamviewerclassroom/api_key',
		teamviewerclassroom_get_string('setting_api_key'),
		teamviewerclassroom_get_string('setting_api_key_description'),
		'', PARAM_TEXT));
}
