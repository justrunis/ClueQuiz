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
        $message = "Question updated successfully";
    } else {
        // Insert a new record
        $DB->insert_record('cluequiz_questions', $data);
        $message = "Question added successfully";
    }

    // Display a success message and redirect back to the same page.
    redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clue'])) {
    // Get the form data
    $question_id = $question->id;
    $clues = $_POST['clue'];

    // Insert the clues into the table
    foreach ($clues as $key => $clue) {
        $data = new stdClass();
        $data->question_id = $question_id;
        $data->clue_text = $clue['clue_text'];
        $data->clue_interval = $clue['clue_interval'];

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
    redirect($PAGE->url, "Clues are added", null, \core\output\notification::NOTIFY_SUCCESS);
}


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('addquestion', 'mod_cluequiz'));

//$form->display();

?>
    <form method="post" class="mod_cluequiz_question_form m-form">
        <div class="m-form__section">
            <h3 class="m-form__heading"><?php echo get_string('questionheader', 'mod_cluequiz') ?></h3>
            <div class="m-form__row">
                <div class="m-form__label">
                    <label for="id_questiontext"><?php echo get_string('questiontext', 'mod_cluequiz') ?></label>
                </div>
                <div class="m-form__input">
                    <textarea id="id_questiontext" name="questiontext" class="form-control alert-icon" required="required"><?php echo $question->question_text ?></textarea>
                </div>
            </div>
            <div class="m-form__row">
                <div class="m-form__label">
                    <label for="id_answertext"><?php echo get_string('answertext', 'mod_cluequiz') ?></label>
                </div>
                <div class="m-form__input">
                    <textarea id="id_answertext" name="answertext" class="form-control alert-icon" required="required"><?php echo $question->answer_text ?></textarea>
                </div>
            </div>
            <div class="m-form__row">
                <div class="m-form__label">
                    <label for="id_timeamount"><?php echo get_string('timeamount', 'mod_cluequiz') ?></label>
                </div>
                <div class="m-form__input">
                    <input id="id_timeamount" name="timeamount" class="form-control alert-icon" required="required" type="number" value="<?php echo $question->time_limit ?>">
                </div>
            </div>
        </div>
        <div class="m-form__actions">
            <button type="submit" class="btn btn-primary" style="margin-top:10px"><?php echo get_string('save', 'mod_cluequiz') ?></button>
        </div>
    </form>

    <form method="post">
        <div id="clues-container">
            <?php
            $existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question->id));
            $clue_index = 1;

            foreach ($existing_clues as $existing_clue) {
                ?>
                <div class="form-group row" id="clue-<?php echo $existing_clue->id; ?>">
                    <label for="id_clue<?php echo $clue_index; ?>" class="col-md-3 col-form-label d-flex pb-0 pr-md-0"><?php echo get_string('clue', 'mod_cluequiz') . ' ' . $clue_index; ?></label>
                    <div class="col-md-9">
                        <textarea name="clue[<?php echo $existing_clue->id; ?>][clue_text]" id="id_clue<?php echo $clue_index; ?>" class="form-control"><?php echo $existing_clue->clue_text; ?></textarea>
                        <input type="hidden" name="clue[<?php echo $existing_clue->id; ?>][clue_interval]" value="<?php echo $existing_clue->clue_interval; ?>">
                        <button type="button" class="btn btn-danger remove-clue" data-id="<?php echo $existing_clue->id; ?>">Remove</button>
                    </div>
                </div>
                <?php
                $clue_index++;
            }
            ?>
        </div>
        <button type="button" id="add-clue" class="btn btn-primary"><?php echo get_string('addclue', 'mod_cluequiz'); ?></button>
        <button type="submit" class="btn btn-success"><?php echo get_string('saveclues', 'mod_cluequiz'); ?></button>
    </form>


    <script>
        // Add more clues button functionality
        var addClueBtn = document.querySelector('#add-clue');
        var clueContainer = document.querySelector('#clues-container');
        var clueIndex =  <?php echo $clue_index - 1 ?>;

        addClueBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clueIndex++;

            // Create the clue field
            var clueField = document.createElement('div');
            clueField.classList.add('form-group', 'row');

            // Create the hidden field for the clue interval
            var clueIntervalInput = document.createElement('input');
            clueIntervalInput.setAttribute('type', 'hidden');
            clueIntervalInput.setAttribute('name', 'clue[' + clueIndex + '][clue_interval]');
            clueIntervalInput.setAttribute('value', clueIndex);
            clueField.appendChild(clueIntervalInput);

            var clueLabel = document.createElement('label');
            clueLabel.setAttribute('for', 'id_clue' + clueIndex);
            clueLabel.classList.add('col-md-3', 'col-form-label', 'd-flex', 'pb-0', 'pr-md-0');
            clueLabel.innerHTML = '<?php echo get_string('clue', 'mod_cluequiz'); ?> ' + clueIndex;
            clueField.appendChild(clueLabel);

            var clueInput = document.createElement('div');
            clueInput.classList.add('col-md-9');

            var textarea = document.createElement('textarea');
            textarea.setAttribute('name', 'clue[' + clueIndex + '][clue_text]');
            textarea.setAttribute('id', 'id_clue' + clueIndex);
            textarea.setAttribute('class', 'form-control');
            clueInput.appendChild(textarea);

            var removeBtn = document.createElement('button');
            removeBtn.classList.add('btn', 'btn-danger', 'mt-2');
            removeBtn.setAttribute('type', 'button');
            removeBtn.setAttribute('id', 'remove-clue-' + clueIndex);
            removeBtn.innerHTML = 'Remove';
            removeBtn.addEventListener('click', function() {
                clueField.remove();
            });
            clueInput.appendChild(removeBtn);

            clueField.appendChild(clueInput);
            clueContainer.appendChild(clueField);
        });
    </script>


<?php
echo $OUTPUT->footer();


