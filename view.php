<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require __DIR__.'/lib.php';

$id = optional_param('id', 0, PARAM_INT);        // Course module ID
$action = optional_param('action', '', PARAM_TEXT);        // Course module ID

$cm = get_coursemodule_from_id('teamviewerclassroom', $id, 0, false, MUST_EXIST);
$teamviewerclassroom = $DB->get_record('teamviewerclassroom', array('id' => $cm->instance), '*', MUST_EXIST);

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/teamviewerclassroom:view', $context);

// Completion and trigger events.
// url_view($teamviewerclassroom, $course, $cm, $context);

$PAGE->set_url('/mod/teamviewerclassroom/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$teamviewerclassroom->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($teamviewerclassroom);

if ($action == 'join_session') {
	if (!confirm_sesskey()) {
		print_error('invalidsesskey');
	}

	// if (!teamviewerclassroom_is_teacher() && $teamviewerclassroom->moderator_required && !$teamviewerclassroom->session_id) {
	// 	// joining not allowed, just display default page
	// } else {
	if (!$teamviewerclassroom->session_id) {
		// create if not yet created
		$ret = teamviewerclassroom_api_post('rest/v1/appointment', teamviewerclassroom_prepare_appointment_forapi_call($teamviewerclassroom));

		if (!$ret || !@$ret->id) {
			if (@$ret->description) {
				echo 'Error: '.$ret->description."<br/>\n";
			}

			die("Couldn't connect to TeamViewer API at ".get_config('teamviewerclassroom', 'server_url'));
		}

		$DB->update_record('teamviewerclassroom', [
			'session_id' => $ret->id,
			'public_url' => $ret->urls->publicUrl,
			'agent_url' => $ret->urls->agentUrl,
			'id' => $cm->instance,
		]);

		// reload teamviewerclassroom
		$teamviewerclassroom = $DB->get_record('teamviewerclassroom', array('id' => $cm->instance), '*', MUST_EXIST);
	}

	if (teamviewerclassroom_is_teacher()) {
		// teacher joins via agent_url
		header("Location: ".$teamviewerclassroom->agent_url);
	} else {
		header("Location: ".$teamviewerclassroom->public_url);
	}

	exit;
	// }
}

if ($action == 'close_session') {
	if (!confirm_sesskey()) {
		print_error('invalidsesskey');
	}

	if (!teamviewerclassroom_is_teacher()) {
		die('not allowed #e9f0sfdso');
	}

	if ($teamviewerclassroom->session_id) {
		$ret = teamviewerclassroom_api_delete('rest/v1/appointment/'.$teamviewerclassroom->session_id);
	}

	$DB->update_record('teamviewerclassroom', [
		'session_id' => '',
		'public_url' => '',
		'agent_url' => '',
		'id' => $cm->instance,
	]);

	header("Location: ".$_SERVER['REQUEST_URI']);
}

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

$session_openingtime_status = teamviewerclassroom_get_session_openingtime_status();
$session_open = $session_openingtime_status == 'open';

echo $OUTPUT->header();

echo '<h2>'.teamviewerclassroom_get_string('meeting').': '.$teamviewerclassroom->name.'</h2>';

if ($teamviewerclassroom->intro) {
	echo '<div style="padding: 5px 0 15px 0">';
	echo '<b>'.teamviewerclassroom_get_string('mod_form_intro').':</b><br/>';
	echo nl2br(htmlspecialchars(trim($teamviewerclassroom->intro)));
	echo '</div>';
}

if ($session_openingtime_status == 'not_started') {
	echo '<div style="padding-bottom: 20px; font-weight: bold">'.teamviewerclassroom_get_string('view_status_not_started').'</div>';
} else if ($session_openingtime_status == 'ended') {
	echo '<div style="padding-bottom: 20px; font-weight: bold">'.teamviewerclassroom_get_string('view_status_ended').'</div>';
} else if (!$teamviewerclassroom->session_id) {
	if ($teamviewerclassroom->enable_waiting_room) {
		echo '<div style="padding-bottom: 20px; font-weight: bold">'.teamviewerclassroom_get_string('view_status_waiting_moderator').'</div>';
	} else {
		echo '<div style="padding-bottom: 20px; font-weight: bold">'.teamviewerclassroom_get_string('view_status_waiting_users').'</div>';
	}
} else {
	$session_status = teamviewerclassroom_get_session_status($teamviewerclassroom);
	// $session_status = teamviewerclassroom_api_get('rest/v1/appointment/'.$teamviewerclassroom->session_id.'/running');

	echo '<div style="padding-bottom: 20px; font-weight: bold">'.teamviewerclassroom_get_string('view_status_meeting_started').'</div>';

	echo '<div style="padding-bottom: 20px">';
	if ($session_status->moderatorOnline) {
		echo teamviewerclassroom_get_string('view_status_moderator_joined').' ';
	}

	echo teamviewerclassroom_get_string('view_status_users_joined', (int)$session_status->participantCount);
	echo '</div>';
}


if ($teamviewerclassroom->openingtime || $teamviewerclassroom->closingtime) {
	echo '<div style="padding-bottom: 20px">'.teamviewerclassroom_get_string('view_availability').':';
	if ($teamviewerclassroom->openingtime) {
		echo ' '.teamviewerclassroom_get_string('view_from').' '.userdate($teamviewerclassroom->openingtime);
	}
	if ($teamviewerclassroom->closingtime) {
		echo ' '.teamviewerclassroom_get_string('view_to').' '.userdate($teamviewerclassroom->closingtime);
	}
	echo '</div>';
}

if ($session_open) {
	teamviewerclassroom_print_join_link();

	if (teamviewerclassroom_is_teacher()) {
		/*
		if (false && $teamviewerclassroom->agent_url) {
			// disabled
			?>
			<input type="button" class="btn btn-secondary"
				   value="<?php echo teamviewerclassroom_get_string('Session verwalten'); ?>"
				   onclick="window.open('<?php echo $teamviewerclassroom->agent_url; ?>')"/>
			<?php
		}
		*/

		if ($teamviewerclassroom->session_id) {
			?>
            <form style="display: inline-block" method="POST">
                <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>"/>
                <input type="hidden" name="action" value="close_session"/>
                <input type="submit" class="btn btn-secondary"
                       value="<?php echo teamviewerclassroom_get_string('view_btn_end_session'); ?>"/>
            </form>
			<?php
		}
		// } else {
		// 	if ($teamviewerclassroom->moderator_required && !$teamviewerclassroom->session_id) {
		// 		// no join allowed
		// 	} else {
		// 		teamviewerclassroom_print_join_link();
		// 	}
	}
}

?>
    <script>
        function teamviewer_reload_state() {
            $.get(document.location.href, function (ret) {
                var newContent = $(ret).find('div[role="main"]');
                // remove all javascripts
                newContent.find('script').remove();
                $('div[role="main"]').html(newContent.html());
            });

        }

        setInterval(teamviewer_reload_state, 10 * 1000);
    </script>
<?php

echo $OUTPUT->footer();
