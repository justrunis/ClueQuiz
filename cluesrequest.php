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

global $DB, $PAGE;
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Include Moodle configuration file

// Check that the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check that the request is coming from a logged-in user
    require_login();

    // Get the ID of the clue to remove
    $id = required_param('id', PARAM_INT);

    // Delete the clue from the database
    $DB->delete_records('cluequiz_clues', array('id' => $id));

    // Send a success response
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
} else {
    // Send a 404 response if the request method is not POST
    http_response_code(404);
}
