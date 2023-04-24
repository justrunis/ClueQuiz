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
 * @package     mod_gpshunt
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */

define('CLI_SCRIPT', true);
require(__DIR__.'/../../../config.php');
global $CFG, $DB;
require_once("$CFG->dirroot/mod/cluequiz/lib.php");
require_once($CFG->libdir . '/gradelib.php');
define('GRADE_TYPE_VALUE', 1);

use PHPUnit\Framework\TestCase;

// command to run these tests vendor/bin/phpunit tests/cluequiz_tests.php
class cluequiz_tests extends TestCase
{
    public function testCorrectAnswerExists()
    {
        $DB = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get_records', 'get_record'])
            ->getMock();

        $USER = new stdClass();
        $USER->id = 1;

        $question_id = 1;

        $attempts = [
            (object) ['user_id' => 1, 'is_correct' => 0, 'question_id' => 1],
            (object) ['user_id' => 1, 'is_correct' => 1, 'question_id' => 1],
            (object) ['user_id' => 1, 'is_correct' => 0, 'question_id' => 2],
        ];

        $DB->expects($this->once())
            ->method('get_records')
            ->with('cluequiz_attempts', ['user_id' => $USER->id])
            ->willReturn($attempts);

        $result = has_user_answered_correct($DB, $USER, $question_id);

        $this->assertTrue($result);
    }

    public function testCorrectAnswerNotExists()
    {
        $DB = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get_records', 'get_record'])
            ->getMock();

        $USER = new stdClass();
        $USER->id = 1;

        $question_id = 1;

        $attempts = [
            (object) ['user_id' => 1, 'is_correct' => 0, 'question_id' => 1],
            (object) ['user_id' => 1, 'is_correct' => 0, 'question_id' => 2],
        ];

        $DB->expects($this->once())
            ->method('get_records')
            ->with('cluequiz_attempts', ['user_id' => $USER->id])
            ->willReturn($attempts);

        $result = has_user_answered_correct($DB, $USER, $question_id);

        $this->assertFalse($result);
    }

    public function testInvalidQuestionId()
    {
        $DB = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get_records', 'get_record'])
            ->getMock();

        $USER = new stdClass();
        $USER->id = 1;

        $question_id = 'not_a_number';

        $result = has_user_answered_correct($DB, $USER, $question_id);

        $this->assertNull($result);
    }

    private $db;

    protected function setUp(): void
    {
        $this->db = $this->getMockBuilder(stdClass::class)
            ->addMethods(['get_records', 'insert_record', 'delete_records'])
            ->getMock();
    }

    protected function tearDown(): void
    {
        // Clean up the mock object
        $this->db = null;
    }

    public function testCheckSpamIsTrue()
    {
        // Set up test data
        $cm = new stdClass();
        $cm->id = 1;

        $user = new stdClass();
        $user->id = 1;

        $question = new stdClass();
        $question->activity_id = $cm->id;

        $attempt = new stdClass();
        $attempt->user_id = $user->id;
        $attempt->question_id = 1;
        $attempt->timestamp = time() - 90;

        // Configure the mock database object to return data as needed
        $this->db->method('get_records')->willReturn([
            $attempt,
        ]);
        $this->db->method('insert_record')->willReturn(1);
        $this->db->method('delete_records')->willReturn(1);

        // Call the check_spam function
        $cooldown = 120;
        $result = check_spam([$attempt], $cm, $cooldown);

        // Check that the function returned false
        $this->assertTrue($result);
    }

    public function testCheckSpamIsFalse()
    {
        // Set up test data
        $cm = new stdClass();
        $cm->id = 1;

        $user = new stdClass();
        $user->id = 1;

        $question = new stdClass();
        $question->activity_id = $cm->id;

        $attempt = new stdClass();
        $attempt->user_id = $user->id;
        $attempt->question_id = 1;
        $attempt->timestamp = time() - 130;

        // Configure the mock database object to return data as needed
        $this->db->method('get_records')->willReturn([
            $attempt,
        ]);
        $this->db->method('insert_record')->willReturn(1);
        $this->db->method('delete_records')->willReturn(1);

        // Call the check_spam function
        $cooldown = 60;
        $result = check_spam([$attempt], $cm, $cooldown);

        // Check that the function returned true
        $this->assertFalse($result);
    }

    public function testCalculateTimerReturnsCorrectValueWhenThereAreNoClues()
    {
        // Arrange
        $user_timer = new stdClass();
        $user_timer->timemodified = time();

        $existing_clues = [];

        // Act
        $result = calculate_timer($user_timer, $existing_clues);

        // Assert
        $this->assertEquals(0, $result[0]);
    }

    public function testCalculateTimerReturnsCorrectValueWhenThereAreClues()
    {
        // Arrange
        $user_timer = new stdClass();
        $user_timer->timemodified = time() - 181;

        $existing_clues = [
            (object) ['clue_timer' => 2],
            (object) ['clue_timer' => 1],
            (object) ['clue_timer' => 5],
        ];

        // Act
        $result = calculate_timer($user_timer, $existing_clues);

        // Assert
        $this->assertEquals(2, $result[0]);
    }
}
