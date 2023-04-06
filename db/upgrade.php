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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_cluequiz
 * @category    upgrade
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_cluequiz upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_cluequiz_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023040503) {

        // Define table cluequiz_questions to be created.
        $table = new xmldb_table('cluequiz_questions');

        // Adding fields to table cluequiz_questions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('activity_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('answer_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('time_limit', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table cluequiz_questions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('activity_id', XMLDB_KEY_FOREIGN, ['activity_id'], 'cluequiz', ['id']);

        // Conditionally launch create table for cluequiz_questions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Cluequiz savepoint reached.
        upgrade_mod_savepoint(true, 2023040503, 'cluequiz');
    }

    if ($oldversion < 2023040503) {

        // Define table cluequiz_clues to be created.
        $table = new xmldb_table('cluequiz_clues');

        // Adding fields to table cluequiz_clues.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('clue_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('clue_interval', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table cluequiz_clues.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('question_id', XMLDB_KEY_FOREIGN, ['question_id'], 'cluequiz_questions', ['id']);

        // Conditionally launch create table for cluequiz_clues.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Cluequiz savepoint reached.
        upgrade_mod_savepoint(true, 2023040503, 'cluequiz');
    }
    if ($oldversion < 2023040506) {

        // Define table cluequiz_attempts to be created.
        $table = new xmldb_table('cluequiz_attempts');

        // Adding fields to table cluequiz_attempts.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('question_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('answer_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('start_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('end_time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('is_correct', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table cluequiz_attempts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);
        $table->add_key('question_id', XMLDB_KEY_FOREIGN, ['question_id'], 'cluequiz_questions', ['id']);

        // Conditionally launch create table for cluequiz_attempts.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Cluequiz savepoint reached.
        upgrade_mod_savepoint(true, 2023040506, 'cluequiz');
    }

    return true;
}
