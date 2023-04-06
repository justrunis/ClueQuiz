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
 * Library of interface functions and constants.
 *
 * @package     mod_cluequiz
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function cluequiz_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_cluequiz into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_cluequiz_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function cluequiz_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('cluequiz', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_cluequiz in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_cluequiz_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function cluequiz_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('cluequiz', $moduleinstance);
}

/**
 * Removes an instance of the mod_cluequiz from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function cluequiz_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('cluequiz', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('cluequiz', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of mod_cluequiz?
 *
 * This function returns if a scale is being used by one mod_cluequiz
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_cluequiz instance.
 */
function cluequiz_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('cluequiz', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_cluequiz.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_cluequiz instance.
 */
function cluequiz_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('cluequiz', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_cluequiz instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function cluequiz_grade_item_update($moduleinstance, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/cluequiz', $moduleinstance->course, 'mod', 'mod_cluequiz', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_cluequiz instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function cluequiz_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/cluequiz', $moduleinstance->course, 'mod', 'cluequiz',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_cluequiz grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function cluequiz_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/cluequiz', $moduleinstance->course, 'mod', 'mod_cluequiz', $moduleinstance->id, 0, $grades);
}

function display_question_form($question){
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
    <?php
}

function display_clue_form($DB, $question){
    $remove_string = get_string('remove', 'mod_cluequiz');
    ?>
    <form method="post">
        <div id="clues-container">
            <h3 class="m-form__heading"><?php echo get_string('clueheader', 'mod_cluequiz') ?></h3>
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
                        <button type="button" class="btn btn-danger remove-clue mt-2" data-id="<?php echo $existing_clue->id; ?>"><?php echo get_string('remove', 'mod_cluequiz')  ?></button>

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
        var removeBtn = document.querySelectorAll('.remove-clue');
        var clueContainer = document.querySelector('#clues-container');
        var clueIndex =  '<?php echo $clue_index - 1 ?>';
        var removeString = '<?php echo $remove_string ?>';

        removeBtn.forEach(x => {
                x.addEventListener('click', function() {
                    x.parentNode.parentNode.remove();
                });
            });

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
            removeBtn.innerHTML = removeString;
            removeBtn.addEventListener('click', function() {
                clueField.remove();
            });
            clueInput.appendChild(removeBtn);

            clueField.appendChild(clueInput);
            clueContainer.appendChild(clueField);
        });
    </script>
    <?php
}
