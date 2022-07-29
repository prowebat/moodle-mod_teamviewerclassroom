<?php
/**
 * @copyright 2022 onwards, TeamViewer Germany GmbH (contact@teamviewer.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function teamviewerclassroom_supports($feature) {
	switch ($feature) {
		case FEATURE_GROUPS:
			return false;
		case FEATURE_GROUPINGS:
			return false;
		case FEATURE_MOD_INTRO:
			return false;
		case FEATURE_COMPLETION_TRACKS_VIEWS:
			return false;
		case FEATURE_COMPLETION_HAS_RULES:
			return false;
		case FEATURE_GRADE_HAS_GRADE:
			return false;
		case FEATURE_GRADE_OUTCOMES:
			return false;
		case FEATURE_RATE:
			return false;
		case FEATURE_BACKUP_MOODLE2:
			return false;
		case FEATURE_SHOW_DESCRIPTION:
			return false;
		case FEATURE_PLAGIARISM:
			return false;
		case FEATURE_ADVANCED_GRADING:
			return false;

		default:
			return null;
	}
}

function teamviewerclassroom_add_instance($data, $mform = null) {
	global $CFG, $DB;

	$data->intro = @$data->intro ?: '';
	$data->introformat = @$data->introformat ?: 0;

	$data->enable_autok_when_agent_leaves = @$data->enable_autok_when_agent_leaves ?: 0;
	$data->enable_waiting_room = @$data->enable_waiting_room ?: 0;
	$data->enable_waiting_room_auto_join = @$data->enable_waiting_room_auto_join ?: 0;

	$data->timemodified = time();
	$data->id = $DB->insert_record('teamviewerclassroom', $data);

	// $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
	// \core_completion\api::update_completion_date_event($data->coursemodule, 'url', $data->id, $completiontimeexpected);

	return $data->id;
}

/**
 * Update teamviewerclassroom instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function teamviewerclassroom_update_instance($data, $mform) {
	global $CFG, $DB;

	$data->intro = @$data->intro ?: '';
	$data->introformat = @$data->introformat ?: 0;

	$data->enable_autok_when_agent_leaves = @$data->enable_autok_when_agent_leaves ?: 0;
	$data->enable_waiting_room = @$data->enable_waiting_room ?: 0;
	$data->enable_waiting_room_auto_join = @$data->enable_waiting_room_auto_join ?: 0;

	$data->timemodified = time();
	$data->id = $data->instance;

	$DB->update_record('teamviewerclassroom', $data);

	if ($teamviewerclassroom = $DB->get_record('teamviewerclassroom', array('id' => $data->id))) {
		if ($teamviewerclassroom->session_id) {
			$ret = teamviewerclassroom_api_put('rest/v1/appointment/'.$teamviewerclassroom->session_id, teamviewerclassroom_prepare_appointment_forapi_call($data));
		}
	}

	// $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
	// \core_completion\api::update_completion_date_event($data->coursemodule, 'url', $data->id, $completiontimeexpected);

	return true;
}

function teamviewerclassroom_prepare_appointment_forapi_call($teamviewerclassroom) {
	global $USER;

	$openingtime = $teamviewerclassroom->openingtime ?: time();
	$date = userdate($openingtime, '%Y-%m-%dT%H:%M:%S');
	$durationInMinutes = $teamviewerclassroom->closingtime ? $teamviewerclassroom->closingtime - $openingtime : 60 * 24 * 365;

	return [
		"title" => $teamviewerclassroom->name,
		"description" => $teamviewerclassroom->intro,
		"owner" => null, // fullname($USER),
		"durationInMinutes" => $durationInMinutes, // 60 * 24 * 365,
		// "attendees" => [
		// ],
		"date" => $date,
		"timezoneId" => get_user_timezone(), // "Europe/Vienna",
		"validAfter" => 0,
		"validBefore" => 0,
		"validType" => "HOURS",
		"permissions" => [
			"enableAutoKickWhenAgentLeaves" => (bool)$teamviewerclassroom->enable_autok_when_agent_leaves,
			"enableWaitingRoom" => (bool)$teamviewerclassroom->enable_waiting_room,
			"enableWaitingRoomAutoJoinOnAgent" => (bool)$teamviewerclassroom->enable_waiting_room_auto_join,
		],
	];
}


/**
 * Deletes an teamviewerclassroom instance
 *
 * @param $id
 */
function teamviewerclassroom_delete_instance($id) {
	global $DB;

	if (!$teamviewerclassroom = $DB->get_record('teamviewerclassroom', array('id' => $id))) {
		return false;
	}

	$cm = get_coursemodule_from_instance('teamviewerclassroom', $id);
	// \core_completion\api::update_completion_date_event($cm->id, 'url', $id, null);

	// note: all context files are deleted automatically

	if ($teamviewerclassroom->session_id) {
		$ret = teamviewerclassroom_api_delete('rest/v1/appointment/'.$teamviewerclassroom->session_id);
	}

	$DB->delete_records('teamviewerclassroom', array('id' => $teamviewerclassroom->id));

	return true;
}

function teamviewerclassroom_is_teacher() {
	global $COURSE;

	return has_capability('mod/teamviewerclassroom:addinstance', context_course::instance($COURSE->id));
}

function teamviewerclassroom_api_request($method, $path, $data) {
	$url = trim(get_config('teamviewerclassroom', 'server_url'), ' /').
		'/'.$path;
	//		'?access_token='.get_config('teamviewerclassroom', 'api_key');

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

	$headers = [];

	if ($data !== null) {
		$postdata = json_encode($data);
		// curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		$headers[] = 'Content-Type: application/json';
	}


	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$headers[] = 'Authorization: Bearer '.get_config('teamviewerclassroom', 'api_key');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	curl_close($ch);

	if (strtoupper($method) == 'DELETE') {
		return $status_code == 200;
	} else {
		// var_dump([$status_code, $url, $postdata, get_config('teamviewerclassroom', 'api_key')]);
		// echo $result;
		// exit;

		return @json_decode($result);
	}
}

function teamviewerclassroom_api_get($path) {
	return teamviewerclassroom_api_request('GET', $path, null);
}

function teamviewerclassroom_api_post($path, $data) {
	return teamviewerclassroom_api_request('POST', $path, $data);
}

function teamviewerclassroom_api_put($path, $data) {
	return teamviewerclassroom_api_request('PUT', $path, $data);
}

function teamviewerclassroom_api_delete($path) {
	return teamviewerclassroom_api_request('DELETE', $path, null);
}

function teamviewerclassroom_cache_make($cache_area) {
	$cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'mod_teamviewerclassroom', $cache_area);

	return $cache;
}

function teamviewerclassroom_cache_callback($cache_area, $cache_id, $callback, $cachettl = 30) {
	$updatecache = false;

	$cache = teamviewerclassroom_cache_make($cache_area);
	$result = $cache->get($cache_id);
	$now = time();
	if (!$updatecache && !empty($result) && $now < ($result['creation_time'] + $cachettl)) {
		// Use the value in the cache.
		return unserialize($result['data']);
	}

	// Ping again and refresh the cache.
	$cache_data = $callback();

	$cache->set($cache_id, array('creation_time' => time(), 'data' => serialize($cache_data)));

	return $cache_data;
}

function teamviewerclassroom_get_session_status($teamviewerclassroom) {
	return teamviewerclassroom_cache_callback('session_cache', $teamviewerclassroom->session_id, function() use ($teamviewerclassroom) {
		return teamviewerclassroom_api_get('rest/v1/appointment/'.$teamviewerclassroom->session_id.'/running');
	}, 9);
}

function teamviewerclassroom_translate($text) {
	return $text;
}

function teamviewerclassroom_get_string($identifier, $a = null) {
	return get_string($identifier, 'teamviewerclassroom', $a);
}