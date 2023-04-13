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
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

global $DB;
$data = file_get_contents("php://input");
$data = json_decode($data);
$questionId = $data->questionId;
$allCluesIds = array();
foreach ($data->clues as $clue){
    $allCluesIds[] = $clue;
}
$allClues = $DB->get_records('cluequiz_clues', array('question_id' => $questionId));

// Create array for all clues that need to show
$retrievedClues = [];

foreach ($allClues as $clue) {
    if (in_array($clue->id, $allCluesIds)) {
        $retrievedClues[] = $clue;
    }
}

// Encode the $retrievedClues array as a JSON string
echo json_encode($retrievedClues);


// Append the $cluesJson string as a parameter to the URL of the target PHP file
//$targetUrl = '/mod/cluequiz/play.php?clues=' . urlencode($cluesJson);

// Redirect the user to the target PHP file
//header('Location: ' . $targetUrl);
exit();
