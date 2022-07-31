<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function teamviewerclassroom_print_join_link() {
	global $teamviewerclassroom;

	$btn_text = $teamviewerclassroom->enable_waiting_room && !$teamviewerclassroom->session_id && teamviewerclassroom_is_teacher()
		? teamviewerclassroom_get_string('view_btn_start_session')
		: teamviewerclassroom_get_string('view_btn_join_session');

	?>
    <form style="display: inline-block" method="POST" target="_blank" onsubmit="setTimeout(teamviewer_reload_state, 1000)">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>"/>
        <input type="hidden" name="action" value="join_session"/>
        <input type="submit" class="btn btn-primary"
               value="<?php echo $btn_text ?>"/>
    </form>
	<?php
}


function teamviewerclassroom_get_session_openingtime_status() {
	global $teamviewerclassroom;

	$now = time();
	if (!empty($teamviewerclassroom->openingtime) && $now < $teamviewerclassroom->openingtime) {
		// The activity has not been opened.
		return 'not_started';
	}
	if (!empty($teamviewerclassroom->closingtime) && $now > $teamviewerclassroom->closingtime) {
		// The activity has been closed.
		return 'ended';
	}

	// The activity is open.
	return 'open';
}
