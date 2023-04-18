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

function plugin_load_language($plugin, $lang=''){
    global $CFG, $DB, $USER;

    if (empty($lang)) {
        $lang = current_language();
    }

    // Check if user has set country to Lithuania
    if ($USER && $USER->country == 'LT') {
        $lt_lang = $CFG->dirroot . '/mod/cluequiz/lang/lt/' . $plugin . '.php';
        if (file_exists($lt_lang)) {
            require_once($lt_lang);
            return;
        }
    }

    // Otherwise, load the language file from the default location
    $en_lang = $CFG->dirroot . '/mod/cluequiz/lang/en/' . $plugin . '.php';
    if (file_exists($en_lang)) {
        require_once($en_lang);
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
    $editor = editors_get_preferred_editor(FORMAT_HTML);
    $editor->use_editor('id_questiontext', []);
    ?>
    <form method="post" class="mod_cluequiz_question_form m-form">
        <div class="m-form__section">
            <h3 class="m-form__heading"><?php echo get_string('questionheader', 'mod_cluequiz') ?></h3>
            <br>
            <div class="m-form__row">
                <div class="m-form__label">
                    <label for="id_questiontext"><?php echo get_string('questiontext', 'mod_cluequiz') ?></label>
                </div>
                <div class="m-form__input">
                    <textarea style="height: 50px" id="id_questiontext" name="questiontext" class="form-control alert-icon" required="required"
                    ><?php echo $question->question_text ?? ''; ?></textarea>
                </div>
            </div>
            <div class="m-form__row">
                <br>
                <div class="m-form__label">
                    <label for="id_answertext"><?php echo get_string('questionanswertext', 'mod_cluequiz') ?></label>
                </div>
                <div class="m-form__input">
                    <textarea id="id_answertext" name="answertext" class="form-control alert-icon" required="required"
                    ><?php echo $question->answer_text ?? ''; ?></textarea>
                </div>
            </div>
        </div>
        <div class="m-form__actions">
            <button id="save-question" type="submit" class="btn btn-success">
                <?php echo get_string('save', 'mod_cluequiz') ?>
            </button>
        </div>
    </form>
    <?php
}

function display_clue_form($DB, $question, $CFG, $cm){
    $remove_string = get_string('remove', 'mod_cluequiz');
    ?>
    <form method="post">
        <div id="clues-container">
            <h3 class="m-form__heading"><?php echo get_string('clueheader', 'mod_cluequiz') ?></h3>
            <?php
            if (!empty($question)) {
                $existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question->id));
            } else {
                $existing_clues = array();
            }
            $clue_index = 1;

            foreach ($existing_clues as $existing_clue) {
                $editor = editors_get_preferred_editor(FORMAT_HTML);
                $editor->use_editor('id_clue'. $clue_index, []);
                if (isset($existing_clue)): ?>
                    <div class="form-group row clue" id="clue-<?php echo $existing_clue->id; ?>">
                        <label for="id_clue<?php echo $clue_index; ?>" class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                            <?php echo get_string('clue', 'mod_cluequiz') . ' ' . $clue_index; ?>
                        </label>
                        <div class="col-md-9">
                            <label><?php echo get_string('cluetext', 'mod_cluequiz') ?></label>
                            <textarea name="clue[clue_text][]" id="id_clue<?php echo $clue_index; ?>" class="form-control"
                            ><?php echo $existing_clue->clue_text ?? '' ;?></textarea>
                            <label><?php echo get_string('timeamount', 'mod_cluequiz')?></label>
                            <input name="clue[clue_timer][]" id="id_clue<?php echo $clue_index; ?>" class="form-control"
                                   type="number" value="<?php echo $existing_clue->clue_timer ?? ''; ?>"
                                   oninvalid="setCustomValidity(<?php echo get_string('invalidnumbermessage', 'mod_cluequiz') ?>)"
                                   onchange="try{setCustomValidity('')}catch(e){}" />
                            <input type="hidden" name="clue[id][]" value="<?php echo $existing_clue->id; ?>">
                            <button type="submit" class="btn btn-danger mt-2" name="remove-clue" value="<?php echo $existing_clue->id; ?>" onclick="return confirm('Are you sure you want to delete this clue?')">
                                <?php echo get_string('remove', 'mod_cluequiz') ?>
                            </button>

                        </div>
                    </div>
                <?php $clue_index++;endif;
            }
            ?>
        </div>
        <button type="submit" class="btn btn-success"><?php echo get_string('saveclues', 'mod_cluequiz'); ?></button>
    </form>
    <form id="clue-form" method="post">
        <input type="hidden" name="add-clue" value="<?php echo $clue_index; ?>">
        <button type="submit" id="add-clue" class="btn btn-primary">
            <?php echo get_string('addclue', 'mod_cluequiz'); ?>
        </button>
        <a id="to-play" href="<?php echo $CFG->wwwroot ?>/mod/cluequiz/play.php?id=<?php echo $cm->id ?>" class="btn btn-primary">
            <?php echo get_string('play', 'mod_cluequiz'); ?>
        </a>
    </form>


    <?php
}

function display_question($question){
    if (!$question) {
        $html = <<<HTML
            <div id="question-text">
                <h3>%s</h3>
                <strong>%s</strong>
            </div>
        HTML;
        $question_header = get_string('questionheader', 'mod_cluequiz');
        $no_question_text = get_string('noquestion', 'mod_cluequiz');
        printf($html, $question_header, $no_question_text);
    } else {
        $html = <<<HTML
            <div id="question-text">
                <h3>%s</h3>
                <p>%s</p>
            </div>
        HTML;
        $question_header = get_string('questionheader', 'mod_cluequiz');
        $question_text = $question->question_text;
        printf($html, $question_header, $question_text);
    }
}

function display_question_clues($existing_clues, $clueCount, $time_limit, $question, $user_timer){

    $clues_to_show = 0;
    $given_time = 0;
    $user_time = $user_timer->timemodified;
    $last_clue_show_time = $user_time;
    foreach ($existing_clues as $clue){
        $given_time = $clue->clue_timer * 60;
        if($last_clue_show_time + $given_time < time()){
            $last_clue_show_time += $given_time;
            $clues_to_show++;
        } else{
            break;
        }
    }

    $elapsed_time = time() - $last_clue_show_time;
    if($given_time == 0){
        $remainingTime = 0;
    }
    else{
        $remainingTime = $given_time - ($elapsed_time % $given_time);
    }
    $timer_ends = time() + $remainingTime;

    ?>
    <!-- Display timer -->
    <div id="timer-container">
        <h3><?php echo get_string('timertext', 'mod_cluequiz')?></h3>
        <span id="timer"></span>
    </div>
    <!-- Display clues -->
    <input type="hidden" id="clueCount" value="<?php echo $clueCount; ?>">
    <input type="hidden" id="cluesToShow" value="<?php echo $clues_to_show; ?>">
    <input type="hidden" id="timeLimit" value="<?php echo $time_limit; ?>">
    <input type="hidden" id="questionId" value="<?php echo $question->id; ?>">
    <input type="hidden" id="allcluesdisplayed" value="<?php echo get_string('allcluesdisplayed', 'mod_cluequiz'); ?>">
    <button class="btn btn-primary" style="display:none; margin: auto" id="refreshButton" type="hidden" onClick="refresh(this)">
        <?php echo get_string('nextclue', 'mod_cluequiz')?>
    </button>
    <div id="clues">
        <?php
        $index = 1;
        $existing_clues = array_values($existing_clues);
        for($i = 0; $i < min($clues_to_show, count($existing_clues)); $i++){
            echo "<p  id='clue-{$existing_clues[$i]->id}' data-id='{$existing_clues[$i]->id}'><b>" .
                get_string('clue', 'mod_cluequiz') . " $index:</b> <span>{$existing_clues[$i]->clue_text}</span></p>";
            $index++;
        }
        ?>
    </div>
    <script>
        let timerEnds = '<?php echo  $timer_ends * 1000?>';

        function refresh(){
            window.location.reload("Refresh")
        }
    </script>
    <script src="JavaScript/timer.js"></script>
    <?php
}

function display_answer_submit_form($PAGE){
    ?>
    <div id="answer-container">
        <form method="post" class="mod_cluequiz_answer_form m-form">
            <div class="m-form__section">
                <h3 class="m-form__heading"><?php echo get_string('answerheader', 'mod_cluequiz') ?></h3>
                <div class="m-form__row">
                    <div class="m-form__label">
                        <label for="id_answer"><?php echo get_string('answertext', 'mod_cluequiz') ?></label>
                    </div>
                    <div class="m-form__input">
                        <textarea id="id_answer" name="answer" class="form-control alert-icon" required="required"></textarea>
                    </div>
                </div>
            </div>
            <div class="m-form__actions">
                <button type="submit" class="btn btn-primary" style="margin-top:10px">
                    <?php echo get_string('submitanswer', 'mod_cluequiz') ?>
                </button>
                <?php create_back_to_course_button($PAGE->course->id, false); ?>
            </div>
        </form>
    </div>
    <?php
}

function has_user_answered_correct($DB, $USER, $question_id) {
    $attempts = $DB->get_records('cluequiz_attempts', array('user_id' => $USER->id));
    // Check that $question_id is set to a number
    if (!is_numeric($question_id)) {
        return;
    }

    foreach ($attempts as $attempt) {
        if($attempt->is_correct == 1 && $question_id == $attempt->question_id){
            return true;
        }
    }
    return false;
}

function write_cluequiz_user_grade($moduleInstance, $USER, $PAGE, $rawgrade){

    if (!is_object($moduleInstance) || !is_object($USER) || !is_object($PAGE)) {
        throw new InvalidArgumentException(get_string('invalidinputparameters','mod_cluequiz'));
    }

    if (!is_numeric($rawgrade) || $rawgrade < 0 || $rawgrade > 100) {
        throw new InvalidArgumentException(get_string('invalidgradevalue','mod_cluequiz'));
    }

    $item = array(
        'itemname' => $moduleInstance->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => 100,
        'grademin' => 0
    );

    $grade = array(
        'userid' => $USER->id,
        'rawgrade' => $rawgrade,
        'dategraded' => (new DateTime())->getTimestamp(),
        'datesubmitted' => (new DateTime())->getTimestamp(),
    );

    $grades = [$USER->id => (object)$grade];

    grade_update('mod_cluequiz', $PAGE->course->id, 'mod', 'cluequiz', $moduleInstance->id, 0, $grades, $item);
}

function display_correct_answer($question) {
    if (!$question) {
        $html = <<<HTML
            <div id="answer-text">
                <h3>%s</h3>
                <strong>%s</strong>
            </div>
        HTML;

        $answer_header = get_string('answerheader', 'mod_cluequiz');
        $no_answer_text = get_string('noanswer', 'mod_cluequiz');
        printf($html, $answer_header, $no_answer_text);

    } else {
        $html = <<<HTML
            <div id="answer-text">
                <h3>%s</h3>
                <p>%s</p>
            </div>
        HTML;
        $answer_header = get_string('answerheader', 'mod_cluequiz');
        $answer_text = $question->answer_text;
        printf($html, $answer_header, $answer_text);
    }
}

function create_back_to_course_button($courseid, $needCenter) {

    $url = new moodle_url('/course/view.php', array('id' => $courseid));
    // Check that $courseid is set to a number
    if (!is_numeric($courseid)) {
        return;
    }

    $style = 'margin-top:10px';
    if($needCenter){
        $style = 'margin-top:10px; text-align: center; display: inline-block; margin: 0 auto; text-align: center;';
    }

    $link_attributes = array(
        'href' => $url->out(),
        'class' => 'btn btn-primary',
        'style'=>$style,
    );
    echo html_writer::start_tag('a', $link_attributes);
    echo get_string('back', 'mod_cluequiz');
    echo html_writer::end_tag('a');
}

function check_spam($users_attempts, $cm, $cooldown){
    // Check that $cooldown is set to a number
    if (!is_numeric($cooldown)) {
        return;
    }

    foreach ($users_attempts as $attempt){
        // Check if the user has submitted an answer for this question
        if(time() - $attempt->timestamp <= $cooldown){
            $time_left = $cooldown - (time() - $attempt->timestamp);
            $minutes = floor($time_left / 60);
            $seconds = $time_left % 60;
            $time_left_formatted = sprintf("%d min %02d sec", $minutes, $seconds);
            $message = sprintf(get_string('spamtext', 'mod_cluequiz'), $time_left_formatted);
            $_SESSION['message'] = '<div class="alert alert-danger">' . $message . '</div>';
            redirect(new moodle_url('/mod/cluequiz/play.php', array('id' => $cm->id)));
            break;
        }
    }
}

function message_handling($DB, $USER, $question){
    if(!$question){
        return;
    }
    if(has_user_answered_correct($DB, $USER, $question->id)){
        echo '<div class="alert alert-success" role="alert">'.get_string('correctanswer', 'mod_cluequiz').'</div>';
    }

    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}
