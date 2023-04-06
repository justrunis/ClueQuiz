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
 * Display information about all the mod_cluequiz modules in the requested course.
 *
 * @package     mod_cluequiz
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$capabilities = array(
    'mod/cluequiz:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'mod/cluequiz:view' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,
        'captype'     => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'      => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    ),
    'mod/cluequiz:grade' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,
        'captype'     => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'      => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    )
    // Other capabilities for the module...
);
