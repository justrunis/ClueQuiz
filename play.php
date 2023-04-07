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

$time_limit = $DB->get_field('cluequiz_questions', 'time_limit', array('activity_id' => $moduleinstance->id));
$question = $DB->get_record('cluequiz_questions', array('activity_id' => $moduleinstance->id));

$existing_clues = $DB->get_records('cluequiz_clues', array('question_id' => $question->id));
$clueCount = sizeof($existing_clues);

/*var_dump($existing_clues);

die();*/


echo $OUTPUT->header();
?>
    <!-- Display timer -->
    <span id="timer"></span>

    <!-- Display clues -->
    <div id="clues">
        <?php foreach ($existing_clues as $clue):
            echo "<p style='display: none' id='clue-$clue->id'> $clue->clue_text </p>";
         endforeach; ?>
    </div>

    <script>
        // Get time limit from PHP and convert to milliseconds
        const timeLimitInMinutes = <?php echo $time_limit; ?>;
        const timeLimitInMilliseconds = timeLimitInMinutes * 60 * 1000;

        const allClues = document.querySelectorAll("#clues > p");

        // Get clue count from PHP
        const clueCount = <?php echo $clueCount; ?>;

        let startTime = localStorage.getItem('startTime');
        if(!startTime){
            // Start timer
            startTime = new Date().getTime();
            localStorage.setItem('startTime', startTime);
        }

        const timerInterval = setInterval(updateTimer, 100);

        function updateTimer() {
            const now = new Date().getTime();
            const elapsedMilliseconds = now - startTime;
            const remainingMilliseconds = timeLimitInMilliseconds - (elapsedMilliseconds % timeLimitInMilliseconds);

            const temp = Math.floor(elapsedMilliseconds / timeLimitInMilliseconds)
            const remainingClues = clueCount - temp;

            for (let i = 0; i < Math.min(clueCount, temp); i++) {
                allClues[i].style.display = 'block';
            }
            if (remainingClues >= 1) {
                displayTimer(remainingMilliseconds);
            } else {
                // Display final message
                document.getElementById("timer").innerHTML = "All clues are displayed";
                clearInterval(timerInterval);
            }
        }

        function displayTimer(remainingMilliseconds) {
            const hours = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) / (1000 * 60 * 60));
            const minutes = Math.floor((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60 * 60) / (1000 * 60));
            const seconds = Math.floor(((remainingMilliseconds || timeLimitInMilliseconds) % (1000 * 60)) / 1000);
            // console.error( remainingMilliseconds);
            document.getElementById("timer").innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
            document.getElementById("timer").style.display = "block";
        }
    </script>

<?php
echo $OUTPUT->footer();
