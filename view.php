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

if (!is_siteadmin()) {
    redirect(new moodle_url('/mod/cluequiz/play.php', array('id' => $cm->id)));
}

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

$question = $DB->get_record('cluequiz_questions', array('activity_id' => $moduleinstance->id));

$form = new mod_cluequiz_question_form(null, array('cmid' => $cm->id));


// Check if the form has been submitted and the data is valid.
if ($formdata = $form->get_data()) {
    $question = new stdClass();
    $question->activity_id = $moduleinstance->id;
    $question->question_text = $formdata->questiontext;
    $question->answer_text = $formdata->answertext;

    // Insert the new question into the database.
    $questionid = $DB->insert_record('cluequiz_question', $question);

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['questiontext'])
    && isset($_POST['answertext']) && isset($_POST['timeamount'])) {
    // Get the form data
    $data = new stdClass();
    $data->activity_id = $moduleinstance->id;
    $data->question_text = $_POST['questiontext'];
    $data->answer_text = $_POST['answertext'];
    $data->time_limit = $_POST['timeamount'];

    // Check if there is already a record for this activity
    $existing_record = $DB->get_record('cluequiz_questions', array('activity_id' => $moduleinstance->id));

    // Retrieve existing clues from the database
    $existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question->id));
    $clueIndex = count($existing_clues);

    if ($existing_record) {
        // Update the existing record
        $data->id = $existing_record->id;
        $DB->update_record('cluequiz_questions', $data);
        $message = get_string('questionupdated', 'mod_cluequiz');
    } else {
        // Insert a new record
        $DB->insert_record('cluequiz_questions', $data);
        $message = get_string('questionadded', 'mod_cluequiz');
    }

    // Display a success message and redirect back to the same page.
    redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clue'])) {
    // Get the form data
    $question_id = $question->id;
    $clues = $_POST['clue'];

    // Get the existing clues for this question
    $existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question_id));

    // Delete any clues that were removed from the form
    $existing_clue_ids = array();
    foreach ($existing_clues as $existing_clue) {
        $found = false;
        foreach ($clues as $key => $clue) {
            if ($existing_clue->clue_interval == $clue['clue_interval']) {
                $found = true;
                $existing_clue_ids[] = $existing_clue->id; // Add the ID to the array
                break;
            }
        }
        if (!$found) {
            $DB->delete_records('cluequiz_clues', array('id' => $existing_clue->id));

            // Decrement the clue_interval of all remaining clues with a higher clue_interval
            foreach ($existing_clues as $other_clue) {
                if ($other_clue->clue_interval > $existing_clue->clue_interval) {
                    $other_clue->clue_interval--;
                    $DB->update_record('cluequiz_clues', $other_clue);
                }
            }
        }
    }

    $remaining_clue_ids = array_diff(array_map(function ($clue) {
        return $clue->id;
    }, $existing_clues), $existing_clue_ids);

    if (!empty($remaining_clue_ids)) {
        $DB->delete_records_list('cluequiz_clues', 'id', $remaining_clue_ids);
    }

    // Reorder the clues
    usort($clues, function ($a, $b) {
        return $a['clue_interval'] - $b['clue_interval'];
    });

    // Insert or update the remaining clues into the table
    foreach ($clues as $key => $clue) {
        $data = new stdClass();
        $data->question_id = $question_id;
        $data->clue_text = $clue['clue_text'];
        $data->clue_interval = $key + 1;

        // Check if the clue already exists
        $existing_clue = $DB->get_record('cluequiz_clues', array('question_id' => $question_id, 'clue_interval' => $data->clue_interval));
        if ($existing_clue) {
            $data->id = $existing_clue->id;
            $DB->update_record('cluequiz_clues', $data);
        } else {
            $DB->insert_record('cluequiz_clues', $data);
        }
    }

    // Display a success message and redirect back to the same page.
    redirect($PAGE->url, get_string('cluesupdated', 'mod_cluequiz'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addquestion', 'mod_cluequiz'));

//$form->display();
display_question_form($question);
display_clue_form($DB, $question, $CFG, $cm);

echo $OUTPUT->footer();
