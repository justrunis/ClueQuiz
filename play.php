<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_cluequiz.
 *
 * @package     mod_cluequiz
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_cluequiz\form\mod_cluequiz_question_form;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/mod/cluequiz/lib.php');

global $USER, $DB, $PAGE, $OUTPUT;
// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$q = optional_param('q', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('cluequiz', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('cluequiz', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('cluequiz', array('id' => $q), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('cluequiz', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_cluequiz\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('cluequiz', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/cluequiz/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$time_limit = $DB->get_field('cluequiz_questions', 'time_limit', array('activity_id' => $moduleinstance->id));
$question = $DB->get_record('cluequiz_questions', array('activity_id' => $moduleinstance->id));

if($question){
    $existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question->id));
    $user_timer = $DB->get_record('cluequiz_user_timer', array('question_id' => $question->id));
    if(!$user_timer){
        $data = array(
            'user_id' => $USER->id,
            'question_id' => $question->id,
            'timer' => $time_limit * 60, // refactor to seconds
            'timemodified' => time()
        );
        $DB->insert_record('cluequiz_user_timer', $data);
        $user_timer = (object) $data;
    }
}
else{
    $existing_clues = [];
}
$clueCount = sizeof($existing_clues);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answer']) && !has_user_answered_correct($DB, $USER, $question->id)) {
    $user_id = $USER->id;
    $question_id = $question->id;
    $correct_answer = $question->answer_text;
    $rawgrade = 100;

    $users_attempts = $DB->get_records('cluequiz_attempts', array('user_id' => $user_id, 'question_id' => $question_id));
    $cooldown = 60;
    $isSpam = check_spam($users_attempts, $cm, $cooldown);
    if($isSpam){
        redirect(new moodle_url('/mod/cluequiz/play.php', array('id' => $cm->id)));
    }

    // Get the user's submitted answer
    $user_answer = $_POST['answer'];

    // Check if the user's answer is correct
    if(strtolower(str_replace(' ', '', $user_answer)) == strtolower(str_replace(' ', '', $correct_answer))){
        $is_correct = 1;
        write_cluequiz_user_grade($moduleinstance, $USER, $PAGE, $rawgrade, $CFG);
    }
    else{
        $is_correct = 0;
        $_SESSION['message'] = '<div class="alert alert-danger">' . get_string('incorrectanswer', 'mod_cluequiz') . '</div>';
    }

    // Insert the user's answer into the database
    $data = array(
        'user_id' => $user_id,
        'question_id' => $question_id,
        'answer_text' => $user_answer,
        'is_correct' => $is_correct,
        'timestamp' => time()
    );
    $DB->insert_record('cluequiz_attempts', $data);

    // Redirect to the same page to prevent form resubmission
    redirect(new moodle_url('/mod/cluequiz/play.php', array('id' => $cm->id)));
}

echo $OUTPUT->header();
message_handling($DB, $USER, $question);

display_question($question);
if($question){
    if(!has_user_answered_correct($DB, $USER, $question->id)){
        display_question_clues($existing_clues, $clueCount, $time_limit, $question, $user_timer);
        display_answer_submit_form($PAGE);
        //user_timer($user_timer);
    }
    else{
        display_correct_answer($question);
        create_back_to_course_button($PAGE->course->id, true);
    }
}
else{
    display_correct_answer($question);
    create_back_to_course_button($PAGE->course->id, true);
}

echo $OUTPUT->footer();
