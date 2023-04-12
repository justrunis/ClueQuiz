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
 * Plugin strings are defined here.
 *
 * @package     mod_cluequiz
 * @category    string
 * @copyright   2023 Justinas Runevicius <justinas.runevicius@distance.ktu.lt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Užuominų viktorina';
$string['modulename'] = 'Užuominų viktorina';
$string['modulenameplural'] = 'UŽUOMINŲ VIKTORINA';
$string['modulenameicon'] = '<img src="'.$CFG->wwwroot.'/mod/cluequiz/pix/icon.svg" class="icon" alt="Clue Quiz icon" />';

$string['cluequizname'] = 'Užuominų viktorinos pavadinimas';
$string['cluequizname_help'] = 'Nustatykite veiklos pavadinimą';
$string['pluginadministration'] = 'Įskiepio administratorius';

$string['addquestion'] = 'Sukurti klausimą su užuominimis';
$string['questionheader'] = 'Klausimas';
$string['questiontext'] = 'Klausimo tekstas';
$string['questionanswertext'] = 'Atsakymo tekstas';
$string['timeamount'] = 'Liakas tarp užuominų (min)';
$string['save'] = 'Išsaugoti klausimą';
$string['questionadded'] = 'Sėkmingai pridėtas klausimas';
$string['questionupdated'] = 'Sėkmingai atnaujintas klausimas';

$string['clueheader'] = 'Užuominos';
$string['addmoreclues'] = 'Pridėti daugiau užuominų';
$string['cluetext'] = 'Užuominos tekstas';
$string['addclue'] = 'Pridėti užuominą';
$string['clue'] = 'Užuomina';
$string['cluesupdated'] = 'Sėkmingai atnaujintos užuominos';

$string['saveclues'] = 'Išsaugoti užuominas';
$string['remove'] = 'Pašalinti';

$string['play'] = 'Pradėti žaidimą';

$string['timertext'] = 'Laikas iki sekančios užuominos';
$string['answerheader'] = 'Atsakymas';
$string['answertext'] = 'Įveskite savo atsakymą';
$string['submitanswer'] = 'Pateikti atsakymą';
$string['back'] = 'Atgal';

$string['correctanswer'] = 'Jūsų atsakymas teisingas. Norėdami grįžti atgal, paspauskite atgal';
$string['incorrectanswer'] = 'Jūsų atsakymas neteisingas, bandykite dar kartą';
$string['spamtext'] = 'Stop! Atrodo kad per greitai pateikiate per daug atsakymų. Bandykite dar kartą už %s.';

$string['noquestion'] = 'Klausimas nepateiktas';
$string['noanswer'] = 'Atsakymas nepateiktas';